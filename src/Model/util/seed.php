<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Everesh\ZeroX45\Model\Database;
use Everesh\ZeroX45\Model\LogAction;

require __DIR__ . "/../../../vendor/autoload.php";
Dotenv::createImmutable(__DIR__ . "/../../../")->load();

$conn = (new Database())->get();

// reset
$conn->executeStatement("SET FOREIGN_KEY_CHECKS = 0");
foreach (
    ["affinity", "endorse", "log", "thread", "post", "topic", "user"]
    as $table
) {
    $conn->executeStatement("TRUNCATE TABLE `$table`");
}
$conn->executeStatement("SET FOREIGN_KEY_CHECKS = 1");

// users
$conn->insert("user", [
    "username" => "alice",
    "passwd" => password_hash("alice", PASSWORD_DEFAULT),
]);
$alice = $conn->lastInsertId();

$conn->insert("user", [
    "username" => "bob",
    "passwd" => password_hash("bob", PASSWORD_DEFAULT),
]);
$bob = $conn->lastInsertId();

// topics
$conn->insert("topic", ["creator_id" => $alice, "name" => "general"]);
$general = $conn->lastInsertId();

$conn->insert("topic", ["creator_id" => $bob, "name" => "tech"]);
$tech = $conn->lastInsertId();

// affinities
$conn->insert("affinity", ["id_user" => $alice, "id_topic" => $tech]);
$conn->insert("affinity", ["id_user" => $bob, "id_topic" => $general]);

// thread 1 — alice creates a thread in general
$conn->transactional(function ($conn) use ($alice, $general, &$thread1Post) {
    $conn->insert("post", [
        "parent_id" => null,
        "title" => "Hello everyone",
        "content" => "First post in general, happy to be here.",
        "creator_key" => "u:" . $alice,
    ]);
    $thread1Post = $conn->lastInsertId();

    $conn->insert("thread", [
        "topic_id" => $general,
        "anchor_id" => $thread1Post,
    ]);

    $conn->insert("log", [
        "action" => LogAction::PostCreated->value,
        "post_id" => $thread1Post,
    ]);
});

// thread 2 — anon creates a thread in tech
$conn->transactional(function ($conn) use ($tech, &$thread2Post) {
    $conn->insert("post", [
        "parent_id" => null,
        "title" => "Anyone using DBAL?",
        "content" =>
            "Just discovered doctrine/dbal, pretty nice for raw SQL lovers.",
        "creator_key" => "s:anonsession1",
    ]);
    $thread2Post = $conn->lastInsertId();

    $conn->insert("thread", ["topic_id" => $tech, "anchor_id" => $thread2Post]);

    $conn->insert("log", [
        "action" => LogAction::PostCreated->value,
        "post_id" => $thread2Post,
    ]);
});

// bob replies to thread 1, marking it as seen
$conn->insert("post", [
    "parent_id" => $thread1Post,
    "title" => "Re: Hello everyone",
    "content" => "Welcome! Great to have you.",
    "creator_key" => "u:" . $bob,
]);
$reply = $conn->lastInsertId();

$conn->insert("log", [
    "action" => LogAction::PostCreated->value,
    "post_id" => $reply,
]);
$conn->insert("log", [
    "action" => LogAction::PostSeen->value,
    "post_id" => $thread1Post,
]);

// endorsements
$conn->insert("endorse", [
    "id_post" => $thread1Post,
    "voter_key" => "u:" . $bob,
    "vote" => 1,
]);
$conn->insert("endorse", [
    "id_post" => $thread1Post,
    "voter_key" => "s:anonsession2",
    "vote" => 1,
]);
$conn->insert("endorse", [
    "id_post" => $thread2Post,
    "voter_key" => "u:" . $alice,
    "vote" => -1,
]);

echo "Seeded.\n";
