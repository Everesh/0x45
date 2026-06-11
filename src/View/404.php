
<?php
/**
 * @var     $this   php renderer reference
 */
?>

<?= $this->fetch("decorators/head.php", ["title" => "Home"]) ?>
<?= $this->fetch("partials/header.php") ?>
<?= $this->fetch("layouts/center.php", [
    "main" => "partials/error.php",
    "mainArgs" => [
        "code" => "404",
        "comment" => "page not found",
    ],
    "mainAfter" => "error",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
