<?php
/**
 * @var     $action     string form target url
 * @var     $submit     string submit button label
 * @var     $error      ?string validation feedback
 * @var     $username   ?string previously entered username
 */
?>

<form class="authForm" method="post" action="<?= $action ?>">
    <?php if ($error ?? null): ?>
        <p class="formError"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <input
        type="text"
        name="username"
        placeholder="username"
        value="<?= htmlspecialchars($username ?? "") ?>"
        maxlength="255"
        required
    >
    <input type="password" name="passwd" placeholder="password" required>
    <button hover-data-scramble type="submit"><?= htmlspecialchars(
        $submit,
    ) ?></button>
</form>
