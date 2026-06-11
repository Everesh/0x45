<?php
/**
 * @var     $this       php renderer reference
 * @var     $session    SessionStore
 * @var     $anchor     post
 * @var     $replies    array<parent_id, array<post>>
 */

// guarded as replies.php leans on it across recursive includes
if (!function_exists("asHex")) {
    function asHex(int $n): string
    {
        $sign = $n < 0 ? "-" : "";
        return $sign . "0x" . str_pad(dechex(abs($n)), 3, "0", STR_PAD_LEFT);
    }
}

if (!function_exists("sanitizeUsername")) {
    function sanitizeUsername(string $user): string
    {
        if (str_starts_with($user, "u:")) {
            return substr($user, 2);
        }

        return hash("sha256", substr($user, 2) . $_ENV["SESSION_SALT"]);
    }
}
?>

<article class="thread">
    <div class="thread-anchor" data-after="thread-anchor">
        <div class="postHead">
            <h2><?= htmlspecialchars($anchor["title"]) ?></h2>
            <div>
                <button hover-data-scramble>--</button>
                <p><?= htmlspecialchars(asHex((int) $anchor["rating"])) ?></p>
                <button hover-data-scramble>++</button>
            </div>
        </div>
        <p class="postContent"><?= htmlspecialchars($anchor["content"]) ?></p>
        <h4 style="margin-top: 1.5em;">- <?= $anchor["username"]
            ? htmlspecialchars($anchor["username"])
            : "<em>" .
                htmlspecialchars(sanitizeUsername($anchor["creator_key"])) .
                "</em>" ?></h4>
    </div>
    <div class="thread-leeches" data-after="thread-leeches">
        <?= $this->fetch("partials/replies.php", [
            "session" => $session,
            "replies" => $replies,
            "parentId" => (int) $anchor["id"],
        ]) ?>
    </div>
</article>
