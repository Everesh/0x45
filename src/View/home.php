<?php
/**
 * @var     $this       php renderer reference
 * @var     $posts      array<post>
 * @var     $topic       ?string topic name when reused as a topic page
 * @var     $topicDel    ?string delete url, set when the caller owns the topic
 * @var     $threadError ?string thread creation error to surface in the box
 */
?>

<?= $this->fetch("decorators/head.php", [
    "title" => isset($topic) ? "topic:" . $topic : "Home",
]) ?>
<?= $this->fetch("partials/header.php", ["topic" => $topic ?? null]) ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/list.php",
    "mainArgs" => [
        "posts" => $posts,
        "topic" => $topic ?? null,
        "topicDel" => $topicDel ?? null,
        "threadError" => $threadError ?? null,
    ],
    "mainAfter" => "threads",
    "aside" => "partials/tmp.php",
    "asideArgs" => ["short" => true],
    "asideAfter" => "log",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
