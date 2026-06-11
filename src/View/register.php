<?php
/**
 * @var     $this       php renderer reference
 * @var     $basePath   string defined in public index.php
 * @var     $error      ?string validation feedback
 * @var     $username   ?string previously entered username
 */
?>

<?= $this->fetch("decorators/head.php", ["title" => "Register"]) ?>
<?= $this->fetch("partials/header.php") ?>
<?= $this->fetch("layouts/center.php", [
    "main" => "partials/authForm.php",
    "mainArgs" => [
        "action" => $basePath . "/register",
        "submit" => "Register",
        "error" => $error ?? null,
        "username" => $username ?? "",
    ],
    "mainAfter" => "register",
]) ?>
<?= $this->fetch("decorators/tail.php") ?>
