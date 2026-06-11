<?php
/**
 * @var     $this       php renderer reference
 * @var     $session    SessionStore
 * @var     $anchor     post
 * @var     $replies    array<parent_id, array<post>>
 * @var     $basePath   string defined in public index.php
 */

// guarded as replies.php leans on it across recursive includes
if (!function_exists("asHex")) {
    function asHex(int $n): string
    {
        $sign = $n < 0 ? "-" : "";
        return $sign . "0x" . str_pad(dechex(abs($n)), 3, "0", STR_PAD_LEFT);
    }
} ?>

<article class="thread">
    <div class="thread-anchor" data-after="thread-anchor">
        <?php $mine = $anchor["creator_key"] === $session->key(); ?>
        <div class="postHead">
            <h2><?= htmlspecialchars($anchor["title"]) ?></h2>
            <div
                class="endorse"
                data-url="<?= $basePath .
                    "/post/" .
                    (int) $anchor["id"] .
                    "/endorse" ?>"
            >
                <button hover-data-scramble data-vote="-1"
                    <?= (int) $anchor["my_vote"] === -1 ? 'class="set"' : "" ?>
                >--</button>
                <p><?= htmlspecialchars(asHex((int) $anchor["rating"])) ?></p>
                <button hover-data-scramble data-vote="1"
                    <?= (int) $anchor["my_vote"] === 1 ? 'class="set"' : "" ?>
                >++</button>
            </div>
        </div>
        <p class="postContent"><?= htmlspecialchars($anchor["content"]) ?></p>
        <div class="postActions"><h4 <?= $mine ? ' class="me"' : "" ?>>-
                    <?= str_starts_with($anchor["creator_key"], "s:")
                        ? "<em>" .
                            htmlspecialchars($anchor["username"]) .
                            "</em>"
                        : htmlspecialchars($anchor["username"]) ?></h4>
            <button hover-data-scramble data-leech="reply">reply</button>
            <?php if ($mine): ?>
                <button hover-data-scramble data-leech="edit">edit</button>
                <button hover-data-scramble data-leech="del"
                    data-url="<?= $basePath .
                        "/post/" .
                        (int) $anchor["id"] .
                        "/delete" ?>"
                    data-confirm="this deletes the WHOLE thread, sure?"
                >del</button>
            <?php endif; ?>
        </div>
        <?= $this->fetch("partials/leechBox.php", [
            "postId" => (int) $anchor["id"],
            "anchorId" => (int) $anchor["id"],
        ]) ?>
    </div>
    <div class="thread-leeches" data-after="thread-leeches">
        <?= $this->fetch("partials/replies.php", [
            "session" => $session,
            "replies" => $replies,
            "parentId" => (int) $anchor["id"],
            "anchorId" => (int) $anchor["id"],
        ]) ?>
    </div>
</article>
