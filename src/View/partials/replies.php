<?php
/**
 * Recursive — child partial of thread.php, relies on its asHex()
 *
 * @var     $this       php renderer reference
 * @var     $replies    array<parent_id, array<post>>
 * @var     $parentId   int root of the subtree to print
 */
?>

<?php foreach ($replies[$parentId] ?? [] as $post): ?>
    <div class="reply">
        <div class="postHead">
            <p class="postContent"><?= htmlspecialchars($post["content"]) ?></p>
            <p><?= htmlspecialchars(asHex((int) $post["rating"])) ?></p>
        </div>
        <?= $this->fetch("partials/replies.php", [
            "replies" => $replies,
            "parentId" => (int) $post["id"],
        ]) ?>
    </div>
<?php endforeach; ?>
