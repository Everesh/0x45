<?php
/**
 * @var     $logs       array<log row: action, post_id, timestamp, anchor_id,
 *                      thread_title> the page's recent activity
 * @var     $basePath   string defined in public index.php
 */
$method = [
    "post_created" => "POST",
    "post_patched" => "PUT",
    "post_deleted" => "DELETE",
    "post_seen" => "GET",
];
?>

<ul class="log">
    <?php foreach ($logs as $log): ?>
        <li>
            <a href="<?= $basePath . "/post/" . (int) $log["anchor_id"] ?>">
                <span
                    class="logMethod"
                    data-method="<?= htmlspecialchars($log["action"]) ?>"
                ><?= $method[$log["action"]] ?? "???" ?></span>
                <span class="logTitle"><?= htmlspecialchars(
                    $log["thread_title"] ?? "untitled",
                ) ?></span>
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
