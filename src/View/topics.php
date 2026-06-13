<?php
/**
 * @var     $this   php renderer reference
 * @var     $topics array<topic + thread count>
 * @var     $error  ?string creation error to surface in the box
 * @var     $logs   array recent activity for the aside log
 */
?>

<?= $this->fetch("decorators/head.php", ["title" => "Topics"]) ?>
<?= $this->fetch("partials/header.php") ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/topicList.php",
    "mainArgs" => ["topics" => $topics, "error" => $error ?? null],
    "mainAfter" => "topics",
    "aside" => "partials/log.php",
    "asideArgs" => ["logs" => $logs ?? []],
    "asideAfter" => "log",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
