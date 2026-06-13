<?php
/**
 * Recursive — child partial of thread.php, relies on its asHex()
 *
 * @var     $this       php renderer reference
 * @var     $session    SessionStore
 * @var     $replies    array<parent_id, array<post>>
 * @var     $parentId   int root of the subtree to print
 * @var     $anchorId   int thread anchor, passed through to leechBox
 * @var     $basePath   string defined in public index.php
 */
?>

<?php foreach ($replies[$parentId] ?? [] as $post): ?>
    <div
        class="reply"
        id="post-<?= (int) $post["id"] ?>"
        style="--depth: <?= (int) $post["depth"] ?>"
    >
        <?php if ((int) $post["deleted"] === 1): ?>
            <div class="replyHead">
                <h4><em>[deleted]</em></h4>
            </div>
            <p class="postContent"><em>[deleted]</em></p>
        <?php else: ?>
            <?php
            $mine = $post["creator_key"] === $session->key();
            $manage = $mine || $session->isSuper();
            ?>
            <div class="replyHead">
                <h4<?= $mine ? ' class="me"' : "" ?>><?= str_starts_with(
                    $post["creator_key"],
                    "s:",
                )
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
                        <?= (int) $post["my_vote"] === -1
                                            ? 'class="set"'
                                            : "" ?>
                    >--</button>
                    <p><?= htmlspecialchars(asHex((int) $post["rating"])) ?></p>
                    <button hover-data-scramble data-vote="1"
                        <?= (int) $post["my_vote"] === 1 ? 'class="set"' : "" ?>
                    >++</button>
                </div>
            </div>
            <p class="postContent"><?= htmlspecialchars($post["content"]) ?></p>
            <div class="postActions">
                <button hover-data-scramble data-leech="reply">reply</button>
                <?php if ($manage): ?>
                    <button hover-data-scramble data-leech="edit">edit</button>
                    <button hover-data-scramble data-leech="del"
                        data-url="<?= $basePath .
                                            "/post/" .
                                            (int) $post["id"] .
                                            "/delete" ?>"
                    >del</button>
                <?php endif; ?>
            </div>
            <?= $this->fetch("partials/leechBox.php", [
                                "postId" => (int) $post["id"],
                                "anchorId" => (int) $anchorId,
                            ]) ?>
        <?php endif; ?>
        <?= $this->fetch("partials/replies.php", [
                            "session" => $session,
                            "replies" => $replies,
                            "parentId" => (int) $post["id"],
                            "anchorId" => (int) $anchorId,
                        ]) ?>
    </div>
<?php endforeach; ?>
