<div id="rightDock">
    <main data-after="<?= htmlspecialchars($mainAfter ?? "") ?>">
        <?= $this->fetch($main, $mainArgs) ?>
    </main>
    <aside data-after="<?= htmlspecialchars($asideAfter ?? "") ?>">
        <?= $this->fetch($aside, $asideArgs) ?>
    </aside>
</div>
