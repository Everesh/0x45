<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

use Doctrine\DBAL\Connection;

class ThreadStore
{
    public const PER_PAGE = 25;

    public function __construct(private readonly Connection $db) {}

    /**
     * one page of threads, newest first; the post table has no timestamp
     * so the monotonic anchor id stands in for recency
     *
     * $topicId null lists the whole board, otherwise one topic
     */
    public function page(?int $topicId, int $page): array
    {
        $qb = $this->db
            ->createQueryBuilder()
            ->select("p.*", "COALESCE(SUM(e.vote), 0) AS rating")
            ->from("thread", "t")
            ->leftJoin("t", "post", "p", "t.anchor_id = p.id")
            ->leftJoin("p", "endorse", "e", "e.id_post = p.id")
            ->groupBy("p.id")
            ->orderBy("p.id", "DESC")
            ->setFirstResult(($page - 1) * self::PER_PAGE)
            ->setMaxResults(self::PER_PAGE);

        if ($topicId !== null) {
            $qb->where("t.topic_id = :tid")->setParameter("tid", $topicId);
        }

        return $qb->fetchAllAssociative();
    }

    /** total threads in the same scope, for the pager math */
    public function count(?int $topicId): int
    {
        $qb = $this->db
            ->createQueryBuilder()
            ->select("COUNT(*)")
            ->from("thread", "t");

        if ($topicId !== null) {
            $qb->where("t.topic_id = :tid")->setParameter("tid", $topicId);
        }

        return (int) $qb->fetchOne();
    }
}
