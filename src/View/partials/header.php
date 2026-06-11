<?php
/**
 * @var     $basePath   string defined in public index.php
 * @var     $session    SessionStore renderer attribute set in public index.php
 * @var     $topic      ?string current topic name, "all" when unset
 */
?>

<header data-after="header">
    <a href="<?= $basePath ?>/"><h2>0x45</h2></a>
    <a href="<?= $basePath ?>/topics">
        <p>topic:<?= htmlspecialchars($topic ?? "all") ?></p>
    </a>
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
