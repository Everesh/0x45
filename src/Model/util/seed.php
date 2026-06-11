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
    ["affinity", "endorse", "log", "thread", "post", "topic", "user"] as $table
) {
    $conn->executeStatement("TRUNCATE TABLE `$table`");
}
$conn->executeStatement("SET FOREIGN_KEY_CHECKS = 1");

// users
$conn->insert("user", [
    "username" => "alice",
    "passwd" => password_hash("alice", PASSWORD_DEFAULT),
]);
$aliceId = (int) $conn->lastInsertId();
$alice = "u:" . $aliceId;

$conn->insert("user", [
    "username" => "bob",
    "passwd" => password_hash("bob", PASSWORD_DEFAULT),
]);
$bobId = (int) $conn->lastInsertId();
$bob = "u:" . $bobId;

$anon1 = "s:anonsession1";
$anon2 = "s:anonsession2";

// topics
$conn->insert("topic", ["creator_id" => $aliceId, "name" => "general"]);
$general = (int) $conn->lastInsertId();

$conn->insert("topic", ["creator_id" => $bobId, "name" => "tech"]);
$tech = (int) $conn->lastInsertId();

// affinities
$conn->insert("affinity", ["id_user" => $aliceId, "id_topic" => $tech]);
$conn->insert("affinity", ["id_user" => $bobId, "id_topic" => $general]);

/**
 * inserts a post + its created log, returns the post id
 *
 * title stays null for leeches, only anchors set one
 */
$post = function (
    ?int $parentId,
    string $content,
    string $creatorKey,
    ?string $title = null,
) use ($conn): int {
    $conn->insert("post", [
        "parent_id" => $parentId,
        "title" => $title,
        "content" => $content,
        "creator_key" => $creatorKey,
    ]);
    $id = (int) $conn->lastInsertId();

    $conn->insert("log", [
        "action" => LogAction::PostCreated->value,
        "post_id" => $id,
    ]);

    return $id;
};

/**
 * inserts an anchor post + its thread atomically, returns the anchor id
 */
$thread = function (
    int $topicId,
    string $title,
    string $content,
    string $creatorKey,
) use ($conn, $post): int {
    return $conn->transactional(function () use (
        $conn,
        $post,
        $topicId,
        $title,
        $content,
        $creatorKey,
    ): int {
        $anchorId = $post(null, $content, $creatorKey, $title);
        $conn->insert("thread", [
            "topic_id" => $topicId,
            "anchor_id" => $anchorId,
        ]);

        return $anchorId;
    });
};

// --- general ---

// 1. deep thread — 7 leeches, one chain nested 4 deep
$hello = $thread(
    $general,
    "Hello everyone",
    "First post in general, happy to be here.",
    $alice,
);
$r1 = $post($hello, "Welcome! Great to have you.", $bob);
$r11 = $post($r1, "Seconded, welcome!", $anon1);
$r111 = $post($r11, "Wait, do we know each other?", $alice);
$post($r111, "Everyone knows everyone here.", $bob);
$post($hello, "first.", $anon2);
$r3 = $post($hello, "Make yourself at home.", $alice);
$post($r3, "Cozy place indeed.", $anon1);

// 2. flat thread — 5 leeches, none nested
$reading = $thread(
    $general,
    "What are you reading?",
    "Drop your current book, no judgement.",
    $bob,
);
$post($reading, "The Pragmatic Programmer, again.", $alice);
$post($reading, "Dune, finally.", $anon1);
$post($reading, "PHP release notes, cover to cover.", $anon2);
$post($reading, "Mostly error logs lately.", $bob);
$post($reading, "A weird CSS specification.", $anon1);

// 3. no leeches
$rules = $thread(
    $general,
    "Rules of the board",
    "Be kind. Stay on topic. Endorse generously.",
    $alice,
);

// 4. single leech
$plans = $thread($general, "Weekend plans", "Anything fun happening?", $anon2);
$post($plans, "Refactoring, obviously.", $bob);

// 5. small nested thread
$pets = $thread($general, "Pet thread", "Post your pets.", $bob);
$cat = $post($pets, "My cat sits on the keyboard all day.", $alice);
$post($cat, "Classic pair programming.", $anon1);

// --- tech ---

// 6. small nested thread
$dbal = $thread(
    $tech,
    "Anyone using DBAL?",
    "Just discovered doctrine/dbal, pretty nice for raw SQL lovers.",
    $anon1,
);
$qb = $post($dbal, "Do you use the query builder too?", $bob);
$post($qb, "Until I need a CTE, then raw SQL.", $anon1);

// 7. nested 3 deep
$fonts = $thread(
    $tech,
    "Monospace fonts",
    "What does everyone code in?",
    $alice,
);
$jb = $post($fonts, "JetBrains Mono, the ligatures got me.", $bob);
$lig = $post($jb, "Ligatures are a crime.", $anon2);
$post($lig, "Strong words for != vs ≠.", $bob);

// 8. single leech
$enums = $thread(
    $tech,
    "PHP 8.1 enums",
    "Backed enums finally killed my class constants.",
    $bob,
);
$post($enums, "LogAction::PostSeen approves.", $alice);

// 9. no leeches
$hosting = $thread(
    $tech,
    "Self-hosting tips",
    "Moving off the cloud, what should I know?",
    $anon2,
);

// 10. no leeches
$thread($tech, "Tabs or spaces", "Settling this once and for all.", $anon1);

// seen marks
foreach ([$hello, $dbal, $reading, $fonts] as $seen) {
    $conn->insert("log", [
        "action" => LogAction::PostSeen->value,
        "post_id" => $seen,
    ]);
}

// endorsements
foreach (
    [
        [$hello, $bob, 1],
        [$hello, $anon2, 1],
        [$r1, $alice, 1],
        [$reading, $anon1, 1],
        [$reading, $anon2, 1],
        [$reading, $alice, 1],
        [$rules, $bob, 1],
        [$plans, $alice, -1],
        [$dbal, $alice, -1],
        [$qb, $anon1, 1],
        [$fonts, $bob, 1],
        [$jb, $alice, -1],
        [$enums, $alice, 1],
        [$hosting, $bob, -1],
    ] as [$postId, $voterKey, $vote]
) {
    $conn->insert("endorse", [
        "id_post" => $postId,
        "voter_key" => $voterKey,
        "vote" => $vote,
    ]);
}

echo "Seeded.\n";
