<?php
/**
 * @var     $this       php renderer reference
 * @var     $session    SessionStore
 * @var     $anchor     post
 * @var     $replies    array<parent_id, array<post>>
 * @var     $logs       array recent activity for the aside log
 */
?>

<?= $this->fetch("decorators/head.php", ["title" => $anchor["title"]]) ?>
<?= $this->fetch("partials/header.php", ["topic" => $anchor["topic"]]) ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/thread.php",
    "mainArgs" => [
        "session" => $session,
        "anchor" => $anchor,
        "replies" => $replies,
    ],
    "aside" => "partials/log.php",
    "asideArgs" => ["logs" => $logs ?? []],
    "asideAfter" => "log",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
