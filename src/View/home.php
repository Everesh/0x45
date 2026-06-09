<?= $this->fetch("decorators/head.php", ["title" => "Home"]) ?>
<?= $this->fetch("partials/header.php") ?>
<?= $this->fetch("layouts/rightDock.php", [
    "main" => "partials/tmp.php",
    "mainArgs" => [],
    "aside" => "partials/tmp.php",
    "asideArgs" => ["short" => true],
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
