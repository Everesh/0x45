<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
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
        $posts = $this->db
            ->createQueryBuilder()
            ->select("p.*", "COALESCE(SUM(e.vote), 0) AS rating")
            ->from("thread", "t")
            ->leftJoin("t", "post", "p", "t.anchor_id = p.id")
            ->leftJoin("p", "endorse", "e", "e.id_post = p.id")
            ->groupBy("p.id")
            ->setMaxResults(25)
            ->fetchAllAssociative();

        return $this->view->render($response, "home.php", [
            "sessionId" => session_id(),
            "posts" => $posts,
        ]);
    }
}
