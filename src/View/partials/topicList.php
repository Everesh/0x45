<?php
/**
 * @var     $session     SessionStore renderer attribute set in public index.php
 * @var     $topics      array<topic + thread count>
 * @var     $error       ?string creation error to surface in the box
 * @var     $feedThreads ?int thread count in the caller's feed, null when anon
 * @var     $basePath    string defined in public index.php
 */
if (!function_exists("asHex")) {
    function asHex(int $n): string
    {
        $sign = $n < 0 ? "-" : "";
        return $sign . "0x" . str_pad(dechex(abs($n)), 3, "0", STR_PAD_LEFT);
    }
} ?>

<div class="list">
    <?php if ($session->isLoggedIn()): ?>
        <a style="border: 1px dotted var(--border-color);" class="listItem" data-topic-new>
            <div>
                <h4 hover-data-scramble>topic:new</h4>
            </div>
            <p>++</p>
        </a>
        <div
            class="leechBox<?= isset($error) && $error !== null
                ? " open"
                : "" ?>"
            data-after="topic"
        >
            <form method="post" action="<?= $basePath ?>/topic/new">
                <?php if (isset($error) && $error !== null): ?>
                    <p class="formError"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <input type="text" name="name" maxlength="32" required>
                <div>
                    <button hover-data-scramble type="submit">create</button>
                    <button hover-data-scramble type="button" data-cancel
                    >cancel</button>
                </div>
            </form>
        </div>
        <a class="listItem" href="<?= $basePath ?>/feed">
            <div>
                <h4>topic:feed</h4>
            </div>
            <p><?= htmlspecialchars(asHex((int) ($feedThreads ?? 0))) ?></p>
        </a>
    <?php endif; ?>
    <?php foreach ($topics as $topic): ?>
        <a
            class="listItem"
            href="<?= $basePath . "/topic/" . $topic["name"] ?>"
        >
            <div>
                <h4>topic:<?= htmlspecialchars($topic["name"]) ?></h4>
            </div>
            <p><?= htmlspecialchars(asHex((int) $topic["threads"])) ?></p>
        </a>
    <?php endforeach; ?>
</div>
