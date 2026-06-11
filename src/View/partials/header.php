<?php
/**
 * @var     $basePath   string defined in public index.php
 * @var     $session    SessionStore renderer attribute set in public index.php
 * @var     $topic      ?string current topic name, "all" when unset
 */
?>

<header data-after="header">
    <a href="<?= $basePath ?>/"><h2>0x45</h2></a>
    <div class="topicCrumb">
        <a href="<?= $basePath ?>/topics"><p hover-data-scramble>topic</p></a>
        <p>:</p>
        <a href="<?= isset($topic) && $topic !== null
            ? $basePath . "/topic/" . htmlspecialchars($topic)
            : $basePath . "/" ?>">
            <p hover-data-scramble><?= htmlspecialchars($topic ?? "all") ?></p>
        </a>
    </div>
    <?php if ($session->isLoggedIn()): ?>
        <p><?= htmlspecialchars($session->username()) ?></p>
        <form method="post" action="<?= $basePath ?>/logout">
            <button hover-data-scramble>Log Out</button>
        </form>
    <?php else: ?>
        <a href="<?= $basePath ?>/login">
            <button hover-data-scramble>Log In</button>
        </a>
        <a href="<?= $basePath ?>/register">
            <button hover-data-scramble>Register</button>
        </a>
    <?php endif; ?>
</header>
