<?php
/**
 * Collapsed reply/edit form, opened + retargeted by js/leech.js
 *
 * @var     $basePath   string defined in public index.php
 * @var     $postId     int post this box acts on
 * @var     $anchorId   int thread anchor, target of the redirect back
 */
?>

<div class="leechBox" data-after="leech">
    <form
        method="post"
        action=""
        data-reply="<?= $basePath . "/post/" . (int) $postId . "/reply" ?>"
        data-edit="<?= $basePath . "/post/" . (int) $postId . "/edit" ?>"
    >
        <input type="hidden" name="anchor" value="<?= (int) $anchorId ?>">
        <textarea name="content" rows="4" required></textarea>
        <div>
            <button hover-data-scramble type="submit">send</button>
            <button hover-data-scramble type="button" data-cancel
            >cancel</button>
        </div>
    </form>
</div>
