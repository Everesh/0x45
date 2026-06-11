<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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
        $voterKey = $request->getAttribute("session")->key();

        $anchor = $this->db
            ->createQueryBuilder()
            ->select(
                "p.*",
                "COALESCE(SUM(e.vote), 0) AS rating",
                "u.username",
                "my.vote AS my_vote",
            )
            ->from("thread", "t")
            ->leftJoin("t", "post", "p", "t.anchor_id = p.id")
            ->leftJoin("p", "endorse", "e", "e.id_post = p.id")
            ->leftJoin("p", "user", "u", "CONCAT('u:', u.id) = p.creator_key")
            ->leftJoin(
                "p",
                "endorse",
                "my",
                "my.id_post = p.id AND my.voter_key = :key",
            )
            ->groupBy("p.id")
            ->addGroupBy("u.username")
            ->addGroupBy("my.vote")
            ->where("p.id = :id")
            ->setParameter("id", $args["id"])
            ->setParameter("key", $voterKey)
            ->fetchAssociative();

        if (!$anchor) {
            return $this->view->render($response, "404.php")->withStatus(404);
        }

        $anchor["username"] ??= $this->fallbackUsername($anchor["creator_key"]);

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
                SELECT
                    d.*,
                    COALESCE(r.rating, 0) AS rating,
                    u.username,
                    my.vote AS my_vote
                FROM descendant d
                LEFT JOIN (
                    SELECT id_post, SUM(vote) AS rating
                    FROM endorse
                    GROUP BY id_post
                ) r ON r.id_post = d.id
                LEFT JOIN user u ON CONCAT('u:', u.id) = d.creator_key
                LEFT JOIN endorse my
                    ON my.id_post = d.id AND my.voter_key = ?
                ORDER BY d.depth, d.id
                SQL
                ,
                [$args["id"], $voterKey],
            )
            ->fetchAllAssociative();

        // parent_id => arr<post>, lets the view recurse from the anchor down
        $replies = [];
        foreach ($descendants as $post) {
            $post["username"] ??= $this->fallbackUsername($post["creator_key"]);
            $replies[(int) $post["parent_id"]][] = $post;
        }

        return $this->view->render($response, "post.php", [
            "sessionId" => session_id(),
            "session" => $request->getAttribute("session"),
            "anchor" => $anchor,
            "replies" => $replies,
        ]);
    }

    /**
     * upserts the callers vote, sending the same vote again retracts it
     *
     * @param $args array<URL PARAM>
     */
    public function endorse(
        Request $request,
        Response $response,
        array $args,
    ): Response {
        $vote = (int) ($request->getParsedBody()["vote"] ?? 0);
        if (!in_array($vote, [-1, 1], true)) {
            return $this->view->render($response, "400.php")->withStatus(400);
        }

        $postId = (int) $args["id"];
        $voterKey = $request->getAttribute("session")->key();

        $current = $this->db->fetchOne(
            "SELECT vote FROM endorse WHERE id_post = ? AND voter_key = ?",
            [$postId, $voterKey],
        );

        try {
            if ($current !== false && (int) $current === $vote) {
                $this->db->delete("endorse", [
                    "id_post" => $postId,
                    "voter_key" => $voterKey,
                ]);
            } else {
                $this->db->executeStatement(
                    "INSERT INTO endorse (id_post, voter_key, vote)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE vote = ?",
                    [$postId, $voterKey, $vote, $vote],
                );
            }
        } catch (ForeignKeyConstraintViolationException) {
            return $this->view->render($response, "404.php")->withStatus(404);
        }

        $rating = (int) $this->db->fetchOne(
            "SELECT COALESCE(SUM(vote), 0) FROM endorse WHERE id_post = ?",
            [$postId],
        );

        // resulting state of the callers vote, 0 when retracted
        $myVote = $current !== false && (int) $current === $vote ? 0 : $vote;

        $response
            ->getBody()
            ->write(json_encode(["rating" => $rating, "vote" => $myVote]));

        return $response->withHeader("Content-Type", "application/json");
    }

    /**
     * display name for posts whose creator_key has no user row:
     * anons get a salted hash of their session id, orphaned
     * user keys fall back to the raw id
     */
    private function fallbackUsername(string $creatorKey): string
    {
        if (str_starts_with($creatorKey, "u:")) {
            return substr($creatorKey, 2);
        }

        return hash("sha256", substr($creatorKey, 2) . $_ENV["SESSION_SALT"]);
    }
}
