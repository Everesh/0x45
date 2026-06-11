<?php
/**
 * @var     $this       php renderer reference
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
} ?>

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
    </div>
    <div class="thread-leeches" data-after="thread-leeches">
        <?= $this->fetch("partials/replies.php", [
            "replies" => $replies,
            "parentId" => (int) $anchor["id"],
        ]) ?>
    </div>
</article>
