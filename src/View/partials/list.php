<div class="list">
  <?php foreach ($posts as $post): ?>
      <a class="listItem"  href="<?= $basePath . "/post/" . $post["id"] ?>">
          <div>
              <h4><?= htmlspecialchars($post["title"]) ?></h4>
              <p><?= htmlspecialchars($post["content"]) ?></p>
          </div>
          <p><?php
          $n = (int) $post["rating"];
          $sign = $n < 0 ? "-" : "";
          echo $sign . "0x" . str_pad(dechex(abs($n)), 4, "0", STR_PAD_LEFT);
          ?></p>
    </a>
  <?php endforeach; ?>
</div>
