<?php
/**
 * @var     $this       php renderer reference
 * @var     $posts      array<post>
 * @var     $topic       ?string topic name when reused as a topic page
 * @var     $topicDel    ?string delete url, set when the caller owns the topic
 * @var     $threadError ?string thread creation error to surface in the box
 * @var     $logs        array recent activity for the aside log
 * @var     $following   ?bool follow state for the topic page button
 * @var     $feed        ?bool whether this is the personalized feed view
 * @var     $page        int current threads page, 1-based
 * @var     $pages       int total threads pages
 * @var     $pagePath    string route path the pager appends ?page=N to
 */
$feed = $feed ?? false; ?>

<?= $this->fetch("decorators/head.php", [
    "title" => $feed ? "feed" : (isset($topic) ? $topic : "all"),
]) ?>
<?= $this->fetch("partials/header.php", [
    "topic" => $topic ?? null,
    "feed" => $feed,
]) ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/list.php",
    "mainArgs" => [
        "posts" => $posts,
        "topic" => $topic ?? null,
        "topicDel" => $topicDel ?? null,
        "threadError" => $threadError ?? null,
        "following" => $following ?? null,
        "feed" => $feed,
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
