<?php
/**
 * @var     $this       php renderer reference
 * @var     $posts      array<post>
 * @var     $topic       ?string topic name when reused as a topic page
 * @var     $topicDel    ?string delete url, set when the caller owns the topic
 * @var     $threadError ?string thread creation error to surface in the box
 * @var     $logs        array recent activity for the aside log
 * @var     $page        int current threads page, 1-based
 * @var     $pages       int total threads pages
 * @var     $pagePath    string route path the pager appends ?page=N to
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
        "page" => $page ?? 1,
        "pages" => $pages ?? 1,
        "pagePath" => $pagePath ?? "/",
    ],
    "mainAfter" => "threads",
    "aside" => "partials/log.php",
    "asideArgs" => ["logs" => $logs ?? []],
    "asideAfter" => "log",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
