<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
            return $this->view
                ->render($response, "404.php")
                ->withStatus(404);
        }

        $posts = $this->db
            ->createQueryBuilder()
            ->select("p.*", "COALESCE(SUM(e.vote), 0) AS rating")
            ->from("thread", "t")
            ->leftJoin("t", "post", "p", "t.anchor_id = p.id")
            ->leftJoin("p", "endorse", "e", "e.id_post = p.id")
            ->where("t.topic_id = :tid")
            ->setParameter("tid", (int) $topic["id"])
            ->groupBy("p.id")
            ->setMaxResults(25)
            ->fetchAllAssociative();

        $basePath = RouteContext::fromRequest($request)->getBasePath();

        return $this->view->render($response, "home.php", [
            "posts" => $posts,
            "topic" => $topic["name"],
            "topicDel" => $this->ownedBy(
                $topic,
                $request->getAttribute("session"),
            )
                ? $basePath . "/topic/" . $topic["name"] . "/delete"
                : null,
        ]);
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
            return $this->view
                ->render($response, "403.php")
                ->withStatus(403);
        }

        $body = (array) $request->getParsedBody();
        $name = trim((string) ($body["name"] ?? ""));

        if (!preg_match('/^[a-z0-9_-]{1,32}$/', $name)) {
            return $this->view
                ->render($response, "topics.php", [
                    "topics" => $this->allTopics(),
                    "error" =>
                        "1-32 chars, lowercase letters, digits, - and _ only",
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
