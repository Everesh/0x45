<?php
/**
 * Server-rendered hex pager, full reload via ?page=N. Renders nothing
 * for a single page.
 *
 * @var     $page       int current page, 1-based
 * @var     $pages      int total pages
 * @var     $pagePath   string route path to append ?page=N to (basePath-rel)
 * @var     $basePath   string defined in public index.php
 */

if (!function_exists("pageHex")) {
    function pageHex(int $n): string
    {
        // unpadded, unlike list.php's asHex -- page numbers read cleaner
        return "0x" . dechex($n);
    }
}

// 1, last, and the current page +/- 1, clamped + de-duped + sorted; the
// render walks it and drops a gap marker wherever the run skips numbers
$tokens = [];
foreach ([1, $page - 1, $page, $page + 1, $pages] as $n) {
    if ($n >= 1 && $n <= $pages) {
        $tokens[$n] = true;
    }
}
$tokens = array_keys($tokens);
sort($tokens);

$href = fn (int $n): string => htmlspecialchars(
    $basePath . $pagePath . "?page=" . $n,
);
?>

<?php if ($pages > 1): ?>
    <nav class="pager">
        <?php if ($page > 1): ?>
            <a hover-data-scramble href="<?= $href($page - 1) ?>">‹ prev</a>
        <?php else: ?>
            <span class="pagerOff">‹ prev</span>
        <?php endif; ?>

        <?php
        $prev = 0;
foreach ($tokens as $n):
    if ($n - $prev > 1): ?>
                <span class="pagerGap">…</span>
            <?php endif;
    $prev = $n;
    if ($n === $page): ?>
                <span class="pagerCur">‹<?= pageHex($n) ?>›</span>
            <?php else: ?>
                <a hover-data-scramble href="<?= $href(
                    $n,
                ) ?>"><?= pageHex($n) ?></a>
            <?php endif;
endforeach; ?>

        <?php if ($page < $pages): ?>
            <a hover-data-scramble href="<?= $href($page + 1) ?>">next ›</a>
        <?php else: ?>
            <span class="pagerOff">next ›</span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
