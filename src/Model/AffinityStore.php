<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

use Doctrine\DBAL\Connection;

class AffinityStore
{
    public function __construct(private readonly Connection $db) {}

    /** whether the user follows the topic, drives the button state */
    public function has(int $userId, int $topicId): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT 1 FROM affinity WHERE id_user = ? AND id_topic = ?",
            [$userId, $topicId],
        );
    }

    /** flips the follow, returns the resulting following-state */
    public function toggle(int $userId, int $topicId): bool
    {
        if ($this->has($userId, $topicId)) {
            $this->db->delete("affinity", [
                "id_user" => $userId,
                "id_topic" => $topicId,
            ]);

            return false;
        }

        $this->db->insert("affinity", [
            "id_user" => $userId,
            "id_topic" => $topicId,
        ]);

        return true;
    }
}
