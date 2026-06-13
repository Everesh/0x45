<?php
/**
 * @var     $logs       array<log row: action, post_id, timestamp, anchor_id,
 *                      is_anchor, thread_title, post_content, post_deleted>
 *                      the page's recent activity
 * @var     $basePath   string defined in public index.php
 */
$method = [
    "post_created" => "POST",
    "post_patched" => "PUT",
    "post_deleted" => "DELETE",
    "post_seen" => "GET",
]; ?>

<ul class="log">
    <?php foreach ($logs as $log): ?>
        <?php
        // anchor rows reference the thread, leech rows reference the reply
        // itself and deep-link to it inside the thread page
        $isAnchor = (int) $log["is_anchor"] === 1;
        $href =
            $basePath .
            "/post/" .
            (int) $log["anchor_id"] .
            ($isAnchor ? "" : "#post-" . (int) $log["post_id"]);
        $label = $isAnchor
            ? $log["thread_title"] ?? "untitled"
            : "↳ " .
                ((int) $log["post_deleted"] === 1
                    ? "[deleted]"
                    : $log["post_content"]);
        ?>
        <li>
            <a href="<?= $href ?>">
                <span
                    class="logMethod"
                    data-method="<?= htmlspecialchars($log["action"]) ?>"
                ><?= $method[$log["action"]] ?? "???" ?></span>
                <span class="logTitle"><?= htmlspecialchars($label) ?></span>
                <time><?= htmlspecialchars(
                    substr((string) $log["timestamp"], 0, 16),
                ) ?></time>
            </a>
        </li>
    <?php endforeach; ?>
    <?php if (!$logs): ?>
        <li class="logEmpty">no activity</li>
    <?php endif; ?>
</ul>
