<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
use Everesh\ZeroX45\Model\LogStore;
use Everesh\ZeroX45\Model\ThreadStore;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class HomeController
{
    public function __construct(
        private readonly PhpRenderer $view,
        private readonly Connection $db,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $page = max(1, (int) ($request->getQueryParams()["page"] ?? 1));
        $store = new ThreadStore($this->db);
        $pages = max(1, (int) ceil($store->count(null) / ThreadStore::PER_PAGE));
        $page = min($page, $pages);

        return $this->view->render($response, "home.php", [
            "sessionId" => session_id(),
            "posts" => $store->page(null, $page),
            "logs" => (new LogStore($this->db))->recent(),
            "page" => $page,
            "pages" => $pages,
            "pagePath" => "/",
        ]);
    }

    /** personalized stream: threads from the topics the caller follows */
    public function feed(Request $request, Response $response): Response
    {
        $session = $request->getAttribute("session");
        if (!$session->isLoggedIn()) {
            return $this->view->render($response, "403.php")->withStatus(403);
        }

        $userId = (int) $session->user()["id"];
        $page = max(1, (int) ($request->getQueryParams()["page"] ?? 1));
        $store = new ThreadStore($this->db);
        $pages = max(
            1,
            (int) ceil($store->countForUser($userId) / ThreadStore::PER_PAGE),
        );
        $page = min($page, $pages);

        return $this->view->render($response, "home.php", [
            "sessionId" => session_id(),
            "posts" => $store->pageForUser($userId, $page),
            "logs" => (new LogStore($this->db))->recent(),
            "page" => $page,
            "pages" => $pages,
            "pagePath" => "/feed",
            "feed" => true,
        ]);
    }
}
