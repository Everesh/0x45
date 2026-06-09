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
          $abs = abs($n);
          if (strlen(decbin($abs)) <= 3) {
              echo $sign . "0b" . decbin($abs);
          } elseif (strlen(decoct($abs)) <= 3) {
              echo $sign . "0" . decoct($abs);
          } elseif (strlen((string) $abs) <= 3) {
              echo $sign . $abs;
          } else {
              echo $sign . "0x" . dechex($abs);
          }
          ?></p>
    </a>
  <?php endforeach; ?>
</div>
