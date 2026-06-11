<?php
/**
 * @var     $this       php renderer reference
 * @var     $mainAfter  title of the main block
 * @var     $main       reference to the contents of the main block
 * @var     $mainArgs   passthrough for main block args
 * @var     $asideAfter title of the aside block
 * @var     $aside      reference to the contents of the aside block
 * @var     $asideArgs  passthrough for aside block args
 */
?>

<div id="rightDock">
    <main data-after="<?= htmlspecialchars($mainAfter ?? "") ?>">
        <?= $this->fetch($main, $mainArgs) ?>
    </main>
    <aside data-after="<?= htmlspecialchars($asideAfter ?? "") ?>">
        <?= $this->fetch($aside, $asideArgs) ?>
    </aside>
</div>
