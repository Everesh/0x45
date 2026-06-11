<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class PostController
{
    public function __construct(
        private readonly PhpRenderer $view,
        private readonly Connection $db,
    ) {}

    /**
     * @param $args array<URL PARAM>
     */
    public function show(
        Request $request,
        Response $response,
        array $args,
    ): Response {
        // TODO
        //
        // for now this is just Home View trunkated to the specific post

        $posts = $this->db
            ->createQueryBuilder()
            ->select("p.*", "COALESCE(SUM(e.vote), 0) AS rating")
            ->from("thread", "t")
            ->leftJoin("t", "post", "p", "t.anchor_id = p.id")
            ->leftJoin("p", "endorse", "e", "e.id_post = p.id")
            ->groupBy("p.id")
            ->where("p.id = ?")
            ->setParameter(0, $args["id"])
            ->fetchAllAssociative();

        if (empty($posts)) {
            return $this->view->render($response, "404.php")->withStatus(404);
        }

        return $this->view->render($response, "home.php", [
            "sessionId" => session_id(),
            "posts" => $posts,
        ]);
    }
}
