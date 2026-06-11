<?php
/**
 * @var     $this       php renderer reference
 * @var     $mainAfter  title of the main block
 * @var     $main       reference to the contents of the main block
 * @var     $mainArgs   passthrough for main block args
 */
?>

<div id="centerLayout" data-after="<?= htmlspecialchars($mainAfter ?? "") ?>">
    <main>
        <?= $this->fetch($main, $mainArgs) ?>
    </main>
</div>
