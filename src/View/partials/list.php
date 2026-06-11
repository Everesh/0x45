<?php
/**
 * @var     $posts      array<post>
 * @var     $basePath   string defined in public index.php
 */

function asHex(int $n): string
{
    $sign = $n < 0 ? "-" : "";
    return $sign . "0x" . str_pad(dechex(abs($n)), 3, "0", STR_PAD_LEFT);
} ?>

<div class="list">
  <?php foreach ($posts as $post): ?>
      <a class="listItem"  href="<?= $basePath . "/post/" . $post["id"] ?>">
          <div>
              <h4><?= htmlspecialchars($post["title"]) ?></h4>
              <p><?= htmlspecialchars($post["content"]) ?></p>
          </div>
          <p><?= htmlspecialchars(asHex((int) $post["rating"])) ?></p>
    </a>
  <?php endforeach; ?>
</div>
