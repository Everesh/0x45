<?= $this->fetch("decorators/head.php", ["title" => "Home"]) ?>
<?= $this->fetch("partials/header.php") ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/tmp.php",
    "mainArgs" => [],
    "mainAfter" => "threads",
    "aside" => "partials/tmp.php",
    "asideArgs" => ["short" => true],
    "asideAfter" => "log",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
