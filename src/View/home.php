<?= $this->fetch("partials/head.php", ["title" => "Home"]) ?>
<body>
    <p>Hello, 0x45!</p>
    <p>Session ID: <?= htmlspecialchars($sessionId) ?></p>
</body>
<?= $this->fetch("partials/tail.php") ?>
