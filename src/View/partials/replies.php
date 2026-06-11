<?php
/**
 * Recursive — child partial of thread.php, relies on its asHex()
 *
 * @var     $this       php renderer reference
 * @var     $session    SessionStore
 * @var     $replies    array<parent_id, array<post>>
 * @var     $parentId   int root of the subtree to print
 * @var     $basePath   string defined in public index.php
 */
?>

<?php foreach ($replies[$parentId] ?? [] as $post): ?>
    <div class="reply">
        <div class="replyHead">
            <h4><?= str_starts_with($post["creator_key"], "s:")
                ? "<em>" . htmlspecialchars($post["username"]) . "</em>"
                : htmlspecialchars($post["username"]) ?></h4>
            <div
                class="endorse"
                data-url="<?= $basePath .
                    "/post/" .
                    (int) $post["id"] .
                    "/endorse" ?>"
            >
                <button hover-data-scramble data-vote="-1"
                    <?= (int) $post["my_vote"] === -1 ? 'class="set"' : "" ?>
                >--</button>
                <p><?= htmlspecialchars(asHex((int) $post["rating"])) ?></p>
                <button hover-data-scramble data-vote="1"
                    <?= (int) $post["my_vote"] === 1 ? 'class="set"' : "" ?>
                >++</button>
            </div>
        </div>
        <p class="postContent"><?= htmlspecialchars($post["content"]) ?></p>
        <?= $this->fetch("partials/replies.php", [
            "session" => $session,
            "replies" => $replies,
            "parentId" => (int) $post["id"],
        ]) ?>
    </div>
<?php endforeach; ?>
