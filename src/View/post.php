<?php
/**
 * @var     $this       php renderer reference
 * @var     $anchor     post
 * @var     $replies    array<parent_id, array<post>>
 */
?>

<?= $this->fetch("decorators/head.php", ["title" => $anchor["title"]]) ?>
<?= $this->fetch("partials/header.php") ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/thread.php",
    "mainArgs" => ["anchor" => $anchor, "replies" => $replies],
    "aside" => "partials/tmp.php",
    "asideArgs" => ["short" => true],
    "asideAfter" => "log",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
