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

/** patches a post's content + logs it, mirrors PostController::edit */
$edit = function (int $postId, string $content) use ($conn): void {
    $conn->update("post", ["content" => $content], ["id" => $postId]);
    $conn->insert("log", [
        "action" => LogAction::PostPatched->value,
        "post_id" => $postId,
    ]);
};

/**
 * soft-deletes a leech (blanks content, flips the flag) + logs it, the
 * subtree survives -- same as PostController::delete for non-anchors
 */
$softDelete = function (int $postId) use ($conn): void {
    $conn->update("post", ["content" => "", "deleted" => 1], ["id" => $postId]);
    $conn->insert("log", [
        "action" => LogAction::PostDeleted->value,
        "post_id" => $postId,
    ]);
};

/** logs a board-wide view of a post */
$seen = function (int $postId) use ($conn): void {
    $conn->insert("log", [
        "action" => LogAction::PostSeen->value,
        "post_id" => $postId,
    ]);
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

// --- bulk filler + interleaved mutations ---
// the board spans several pages, and the log reads as a live mix instead of
// blocks per action: creates, edits, deletes and views are woven together so
// the id order (what the log sorts on) alternates POST/PUT/DELETE/GET

$voters = [$alice, $bob, $anon1, $anon2];
$quips = [
    "Strong opinions, loosely held.",
    "This again? Love it.",
    "Hard disagree, respectfully.",
    "Finally someone said it.",
    "Bookmarking this.",
    "Counterpoint: no.",
];

// adversarial anchors created up front so the loop can edit / delete / view
// them. every payload here is stored verbatim and must render as literal text:
// templates escape via htmlspecialchars, bound params keep the SQL inert, and
// stored php is never include()d. proof, not exploits.
$xss = $thread(
    $tech,
    "<script>alert('xss')</script>",
    "classic: <script>alert(document.cookie)</script>",
    $anon1,
);
$php = $thread(
    $tech,
    '<?php echo "title hijack"; ?>',
    "stored php stays inert:" . "\n" . '<?php system($_GET[\'c\']); ?>',
    $bob,
);
$sqli = $thread(
    $general,
    "'; DROP TABLE post;-- ",
    "' OR 1=1 -- ' UNION SELECT username, passwd FROM user -- ",
    $anon1,
);
$payloadHosts = [$xss, $php, $sqli];

// a few attached now + named, so the mutation queue can target them
$attr = $post($xss, 'attr breakout " autofocus onfocus=alert(1) x="', $anon1);
$tmpl = $post($php, 'template probes: {{7*7}} ${7*7} #{7*7} <%= 7*7 %>', $alice);
$emoji = $post($sqli, "unicode: 🐛💉 ＜script＞ ‮reversed‬ \t tabs", $alice);

// the rest of the grab-bag (svg, event handlers, breakouts, filter bypass),
// dripped as leeches through the loop so payloads scatter across the log
$payloads = [
    "<svg><script>alert(1)</script></svg>",
    "<svg/onload=alert(document.domain)>",
    "<svg><animate onbegin=alert(1) attributeName=x dur=1s></svg>",
    "<svg><a xlink:href=\"javascript:alert(1)\"><text x=10 y=20>tap</text></a></svg>",
    "<svg width=1 height=1><image href=x onerror=alert(1)></svg>",
    "<svg><foreignObject><script>alert(1)</script></foreignObject></svg>",
    "<svg><set attributeName=onload to=alert(1)></svg>",
    "<img src=x onerror=alert(1)>",
    "<body onload=alert(1)>",
    "<details open ontoggle=alert(1)>",
    "<marquee onstart=alert(1)>scrolling doom</marquee>",
    "<input autofocus onfocus=alert(1)>",
    "<video><source onerror=alert(1)></video>",
    "<iframe src=\"javascript:alert(1)\"></iframe>",
    "<math><mtext><script>alert(1)</script></mtext></math>",
    "<audio src=x onerror=alert(1)>",
    "\"><script>alert(String.fromCharCode(88,83,83))</script>",
    "</title></style></textarea><script>alert(1)</script>",
    "<scr<script>ipt>alert(1)</scr</script>ipt>",
    "<a href=\"data:text/html,<script>alert(1)</script>\">data uri</a>",
    "<a href=\"javascript:alert(document.cookie)\">click me</a>",
    "javascript:/*--></title></style></textarea></script></xmp><svg/onload=alert(1)>",
    "<style>*{background:url('javascript:alert(1)')}</style>",
    "&lt;script&gt;already encoded&lt;/script&gt;",
    "<img src=`x`onerror=alert(1)>",
];

// edits / deletes / views drained one-per-iteration; arrow fns capture the
// target ids + helper closures by value
$mutations = [
    fn () => $seen($hello),
    fn () => $edit($r1, "Welcome! Great to have you. (edited: fixed a typo)"),
    fn () => $seen($r111),
    fn () => $edit($cat, "My cat owns the keyboard. edit: now it's two cats."),
    fn () => $softDelete($r11),
    fn () => $seen($dbal),
    fn () => $edit($dbal, "doctrine/dbal, still nice. EDIT: the QB grew on me."),
    fn () => $edit($attr, "patched into svg: <svg/onload=alert('edited')>"),
    fn () => $seen($attr),
    fn () => $edit($qb, "Do you use the query builder? (revised)"),
    fn () => $seen($qb),
    fn () => $edit($qb, "Do you use the query builder? (revised again, sorry)"),
    fn () => $softDelete($tmpl),
    fn () => $seen($php),
    fn () => $edit($jb, "JetBrains Mono. EDIT: <script>alert('font')</script>"),
    fn () => $seen($reading),
    fn () => $softDelete($emoji),
    fn () => $seen($sqli),
    fn () => $edit($plans, "Anything fun? EDIT: <img src=x onerror=alert(1)>"),
    fn () => $seen($r3),
    fn () => $edit($enums, "Backed enums killed my class constants. (edited)"),
    fn () => $seen($enums),
    fn () => $edit($lig, "Ligatures are a crime. EDIT: fine, != is ok."),
    fn () => $seen($fonts),
    fn () => $seen($xss),
    fn () => $seen($plans),
];

$filler = [
    [$general, "Coffee or tea?", "The eternal morning debate."],
    [$general, "Introduce yourself", "New faces welcome, say hi."],
    [$general, "Best keyboard shortcut", "The one you can't live without."],
    [$tech, "<svg onload=alert(1)>", "payload in the title bar"],
    [$general, "Lurkers, reveal yourselves", "We know you're out there."],
    [$general, "Favorite color scheme", "Post your palette."],
    [$general, "What's on your desk?", "Show the chaos."],
    [$general, "Underrated CLI tools", "The ones nobody talks about."],
    [$tech, "<img src=x onerror=alert(1)>", "img onerror in the title"],
    [$general, "How do you take notes?", "Plain text gang, assemble."],
    [$general, "Music while coding?", "Lyrics or no lyrics?"],
    [$general, "Mechanical or membrane?", "Settle it."],
    [$general, "Your first program", "Be honest, was it Hello World?"],
    [$tech, "</script><script>alert(1)</script>", "script breakout title"],
    [$general, "Dark mode everywhere", "Light mode users, explain yourselves."],
    [$general, "Weekend project ideas", "Drop what you're tinkering with."],
    [$tech, "Vim or Emacs", "The holy war continues."],
    [$tech, "Static vs dynamic typing", "Where do you land?"],
    [$tech, "SQLite is underrated", "One file, zero regrets."],
    [$general, "1'><svg/onload=alert(1)>", "quote breakout title"],
    [$tech, "Regex: love or hate", "Now you have two problems."],
    [$tech, "Container fatigue", "Is bare metal making a comeback?"],
    [$tech, "Favorite HTTP status", "418 supremacy."],
    [$tech, "Monorepo or polyrepo", "Pick your pain."],
    [$tech, "Why I dropped frameworks", "Vanilla everything."],
    [$tech, "The best git alias", "Save my keystrokes."],
    [$tech, "Rewrite it in Rust?", "Or just leave it in C."],
    [$tech, "Cron jobs that haunt you", "The 3am pager story."],
    [$tech, "Comments: yes or no", "Self-documenting code is a myth."],
];

foreach ($filler as $i => [$topicId, $title, $content]) {
    $author = $voters[$i % count($voters)];
    $anchor = $thread($topicId, $title, $content, $author);

    // a reply on every third thread
    if ($i % 3 === 0) {
        $post($anchor, $quips[$i % count($quips)], $voters[($i + 1) % 4]);
    }

    // drip a payload leech so fresh xss POSTs scatter through the log
    if ($payloads) {
        $post(
            $payloadHosts[$i % count($payloadHosts)],
            array_shift($payloads),
            $voters[$i % 4],
        );
    }

    // drain a mutation -> PUT/DELETE/GET weave through the POSTs
    if ($mutations) {
        (array_shift($mutations))();
    }

    // one vote each so ratings aren't a wall of 0x000
    $conn->insert("endorse", [
        "id_post" => $anchor,
        "voter_key" => $voters[($i + 2) % 4],
        "vote" => $i % 4 === 0 ? -1 : 1,
    ]);
}

// drain leftovers so nothing is lost if the queues outlast the loop
while ($payloads) {
    $post($payloadHosts[count($payloads) % 3], array_shift($payloads), $anon2);
}
while ($mutations) {
    (array_shift($mutations))();
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
