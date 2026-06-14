<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

use Doctrine\DBAL\Connection;

class LogStore
{
    private const LIMIT = 60;

    public function __construct(private readonly Connection $db) {}

    /** activity across the whole board, newest first */
    public function recent(): array
    {
        return $this->scoped("", []);
    }

    /** activity on every post belonging to the topic */
    public function forTopic(int $topicId): array
    {
        return $this->scoped("WHERE t.topic_id = ?", [$topicId]);
    }

    /** activity on the anchor and all of its descendants */
    public function forThread(int $anchorId): array
    {
        return $this->scoped("WHERE t.anchor_id = ?", [$anchorId]);
    }

    /**
     * walks down from the seeded anchors carrying each post's anchor_id,
     * so a log on a buried leech still resolves to the thread it links to
     *
     * $seedWhere narrows the seeded anchors, "" means every thread
     */
    private function scoped(string $seedWhere, array $params): array
    {
        return $this->db
            ->executeQuery(
                <<<SQL
                WITH RECURSIVE tree AS (
                    SELECT p.id, p.id AS anchor_id
                    FROM post p
                    JOIN thread t ON t.anchor_id = p.id
                    $seedWhere
                    UNION ALL
                    SELECT p.id, tree.anchor_id
                    FROM post p
                    JOIN tree ON p.parent_id = tree.id
                )
                SELECT
                    l.action,
                    l.post_id,
                    l.timestamp,
                    tree.anchor_id,
                    (l.post_id = tree.anchor_id) AS is_anchor,
                    a.title AS thread_title,
                    p.content AS post_content,
                    p.deleted AS post_deleted
                FROM log l
                JOIN tree ON tree.id = l.post_id
                JOIN post a ON a.id = tree.anchor_id
                JOIN post p ON p.id = l.post_id
                ORDER BY l.id DESC
                LIMIT
                SQL
                .
                    " " .
                    self::LIMIT,
                $params,
            )
            ->fetchAllAssociative();
    }
}
