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
        $anchor = $this->db
            ->createQueryBuilder()
            ->select("p.*", "COALESCE(SUM(e.vote), 0) AS rating")
            ->from("thread", "t")
            ->leftJoin("t", "post", "p", "t.anchor_id = p.id")
            ->leftJoin("p", "endorse", "e", "e.id_post = p.id")
            ->groupBy("p.id")
            ->where("p.id = ?")
            ->setParameter(0, $args["id"])
            ->fetchAssociative();

        if (!$anchor) {
            return $this->view->render($response, "404.php")->withStatus(404);
        }

        // QueryBuilder has no CTE support, hence the raw SQL
        $descendants = $this->db
            ->executeQuery(
                <<<'SQL'
                    WITH RECURSIVE descendant AS (
                        SELECT p.*, 1 AS depth
                        FROM post p
                        WHERE p.parent_id = ?
                        UNION ALL
                        SELECT p.*, d.depth + 1
                        FROM post p
                        JOIN descendant d ON p.parent_id = d.id
                    )
                    SELECT d.*, COALESCE(r.rating, 0) AS rating
                    FROM descendant d
                    LEFT JOIN (
                        SELECT id_post, SUM(vote) AS rating
                        FROM endorse
                        GROUP BY id_post
                    ) r ON r.id_post = d.id
                    ORDER BY d.depth, d.id
                    SQL,
                [$args["id"]],
            )
            ->fetchAllAssociative();

        // parent_id => arr<post>, lets the view recurse from the anchor down
        $replies = [];
        foreach ($descendants as $post) {
            $replies[(int) $post["parent_id"]][] = $post;
        }

        return $this->view->render($response, "post.php", [
            "sessionId" => session_id(),
            "anchor" => $anchor,
            "replies" => $replies,
        ]);
    }
}
