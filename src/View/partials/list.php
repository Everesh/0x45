<?php
/**
 * @var     $posts       array<post>
 * @var     $topic       ?string topic name, the new-thread box rides on it
 * @var     $topicDel    ?string delete url, set when the caller owns the topic
 * @var     $threadError ?string thread creation error to surface in the box
 * @var     $basePath    string defined in public index.php
 */

function asHex(int $n): string
{
    $sign = $n < 0 ? "-" : "";
    return $sign . "0x" . str_pad(dechex(abs($n)), 3, "0", STR_PAD_LEFT);
} ?>

<div class="list">
  <?php if (isset($topic) && $topic !== null): ?>
      <a style="border: 1px dotted var(--border-color);" class="listItem" data-thread-new>
          <div>
              <h4 hover-data-scramble>thread:new</h4>
          </div>
          <p>++</p>
      </a>
      <div
          class="leechBox<?= isset($threadError) && $threadError !== null
              ? " open"
              : "" ?>"
          data-after="thread"
      >
          <form
              method="post"
              action="<?= $basePath . "/topic/" . $topic . "/thread" ?>"
          >
              <?php if (isset($threadError) && $threadError !== null): ?>
                  <p class="formError"><?= htmlspecialchars($threadError) ?></p>
              <?php endif; ?>
              <input type="text" name="title" maxlength="255"
                  placeholder="title" required>
              <textarea name="content" rows="4"
                  placeholder="body" required></textarea>
              <div>
                  <button hover-data-scramble type="submit">create</button>
                  <button hover-data-scramble type="button" data-cancel
                  >cancel</button>
              </div>
          </form>
      </div>
  <?php endif; ?>
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
