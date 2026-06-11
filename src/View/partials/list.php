<?php
/**
 * @var     $posts      array<post>
 * @var     $topicDel   ?string delete url, set when the caller owns the topic
 * @var     $basePath   string defined in public index.php
 */

function asHex(int $n): string
{
    $sign = $n < 0 ? "-" : "";
    return $sign . "0x" . str_pad(dechex(abs($n)), 3, "0", STR_PAD_LEFT);
} ?>

<div class="list">
  <?php if (isset($topicDel) && $topicDel !== null): ?>
      <button hover-data-scramble data-topic-del
          data-url="<?= $topicDel ?>"
          data-confirm="this deletes the topic and ALL its threads, sure?"
      >del_topic</button>
  <?php endif; ?>
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
