<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Everesh\ZeroX45\Model\LogAction;
use Everesh\ZeroX45\Model\LogStore;
use Everesh\ZeroX45\Model\ThreadStore;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;
use Slim\Views\PhpRenderer;

class TopicController
{
    public function __construct(
        private readonly PhpRenderer $view,
        private readonly Connection $db,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, "topics.php", [
            "topics" => $this->allTopics(),
            "logs" => (new LogStore($this->db))->recent(),
        ]);
    }

    public function show(
        Request $request,
        Response $response,
        array $args,
    ): Response {
        $topic = $this->db->fetchAssociative(
            "SELECT * FROM topic WHERE name = ?",
            [$args["name"]],
        );

        if (!$topic) {
            return $this->view->render($response, "404.php")->withStatus(404);
        }

        $basePath = RouteContext::fromRequest($request)->getBasePath();
        $topicId = (int) $topic["id"];

        $page = max(1, (int) ($request->getQueryParams()["page"] ?? 1));
        $store = new ThreadStore($this->db);
        $pages = max(
            1,
            (int) ceil($store->count($topicId) / ThreadStore::PER_PAGE),
        );
        $page = min($page, $pages);

        return $this->view->render($response, "home.php", [
            "posts" => $store->page($topicId, $page),
            "topic" => $topic["name"],
            "topicDel" => $this->ownedBy(
                $topic,
                $request->getAttribute("session"),
            )
                ? $basePath . "/topic/" . $topic["name"] . "/delete"
                : null,
            "logs" => (new LogStore($this->db))->forTopic($topicId),
            "page" => $page,
            "pages" => $pages,
            "pagePath" => "/topic/" . $topic["name"],
        ]);
    }

    /**
     * anchors a new thread under the topic -- open to anyone, same as
     * replies, the creator_key carries the session identity
     *
     * @param $args array<URL PARAM>
     */
    public function createThread(
        Request $request,
        Response $response,
        array $args,
    ): Response {
        $topic = $this->db->fetchAssociative(
            "SELECT * FROM topic WHERE name = ?",
            [$args["name"]],
        );

        if (!$topic) {
            return $this->view->render($response, "404.php")->withStatus(404);
        }

        $session = $request->getAttribute("session");
        $body = (array) $request->getParsedBody();
        $title = trim((string) ($body["title"] ?? ""));
        $content = trim((string) ($body["content"] ?? ""));
        $basePath = RouteContext::fromRequest($request)->getBasePath();

        if ($title === "" || $content === "") {
            $topicId = (int) $topic["id"];
            $store = new ThreadStore($this->db);
            $pages = max(
                1,
                (int) ceil($store->count($topicId) / ThreadStore::PER_PAGE),
            );

            return $this->view
                ->render($response, "home.php", [
                    "posts" => $store->page($topicId, 1),
                    "topic" => $topic["name"],
                    "topicDel" => $this->ownedBy($topic, $session)
                        ? $basePath . "/topic/" . $topic["name"] . "/delete"
                        : null,
                    "threadError" => "title and body are both required",
                    "logs" => (new LogStore($this->db))->forTopic($topicId),
                    "page" => 1,
                    "pages" => $pages,
                    "pagePath" => "/topic/" . $topic["name"],
                ])
                ->withStatus(400);
        }

        // post + thread row in one go, the anchor_id FK demands the
        // post exist first, lastInsertId carries it across the inserts
        $anchorId = 0;
        $this->db->transactional(function (Connection $conn) use (
            $topic,
            $title,
            $content,
            $session,
            &$anchorId,
        ) {
            $conn->insert("post", [
                "parent_id" => null,
                "title" => $title,
                "content" => $content,
                "creator_key" => $session->key(),
            ]);
            $anchorId = (int) $conn->lastInsertId();
            $conn->insert("thread", [
                "topic_id" => (int) $topic["id"],
                "anchor_id" => $anchorId,
            ]);
            $conn->insert("log", [
                "action" => LogAction::PostCreated->value,
                "post_id" => $anchorId,
            ]);
        });

        return $response
            ->withHeader("Location", $basePath . "/post/" . $anchorId)
            ->withStatus(302);
    }

    public function delete(
        Request $request,
        Response $response,
        array $args,
    ): Response {
        $topic = $this->db->fetchAssociative(
            "SELECT * FROM topic WHERE name = ?",
            [$args["name"]],
        );

        if (!$topic) {
            return $response->withStatus(404);
        }

        if (!$this->ownedBy($topic, $request->getAttribute("session"))) {
            return $response->withStatus(403);
        }

        // cascaded FK deletes skip triggers, so the threads go first --
        // trg_thread_after_delete is what removes the anchor posts
        $this->db->transactional(function (Connection $conn) use ($topic) {
            $conn->executeStatement("DELETE FROM thread WHERE topic_id = ?", [
                (int) $topic["id"],
            ]);
            $conn->delete("topic", ["id" => (int) $topic["id"]]);
        });

        $basePath = RouteContext::fromRequest($request)->getBasePath();
        $response
            ->getBody()
            ->write(json_encode(["redirect" => $basePath . "/topics"]));

        return $response->withHeader("Content-Type", "application/json");
    }

    public function create(Request $request, Response $response): Response
    {
        $session = $request->getAttribute("session");

        if (!$session->isLoggedIn()) {
            return $this->view->render($response, "403.php")->withStatus(403);
        }

        $body = (array) $request->getParsedBody();
        $name = trim((string) ($body["name"] ?? ""));

        if (!preg_match('/^[a-z0-9_-]{1,32}$/', $name)) {
            return $this->view
                ->render($response, "topics.php", [
                    "topics" => $this->allTopics(),
                    "error" =>
                        "1-32 chars, lowercase letters, digits, - and _ only",
                    "logs" => (new LogStore($this->db))->recent(),
                ])
                ->withStatus(400);
        }

        try {
            $this->db->insert("topic", [
                "creator_id" => (int) $session->user()["id"],
                "name" => $name,
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->view
                ->render($response, "topics.php", [
                    "topics" => $this->allTopics(),
                    "error" => "topic already exists",
                    "logs" => (new LogStore($this->db))->recent(),
                ])
                ->withStatus(409);
        }

        $basePath = RouteContext::fromRequest($request)->getBasePath();

        return $response
            ->withHeader("Location", $basePath . "/topic/" . $name)
            ->withStatus(302);
    }

    private function ownedBy(array $topic, $session): bool
    {
        if (!$session->isLoggedIn()) {
            return false;
        }

        return $session->isSuper() ||
            ($topic["creator_id"] !== null &&
                (int) $topic["creator_id"] === (int) $session->user()["id"]);
    }

    private function allTopics(): array
    {
        return $this->db
            ->createQueryBuilder()
            ->select("t.name", "COUNT(th.id) AS threads")
            ->from("topic", "t")
            ->leftJoin("t", "thread", "th", "th.topic_id = t.id")
            ->groupBy("t.id")
            ->addGroupBy("t.name")
            ->orderBy("t.name")
            ->fetchAllAssociative();
    }
}
