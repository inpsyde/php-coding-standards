<?php
// @phpcsSniff InpsydeTemplates.Formatting.TrailingSemicolon

?>

<?= 'Test content'; // @phpcsWarningOnThisLine ?>
<?= 'Without trailing semicolon' ?>

<?php
$content = 'New content';
if ($content) {
    echo $content;
}
?>

<?php if ($content) : ?>
    <?= $content ?>
<?php endif; // @phpcsWarningOnThisLine ?>

<?php // @phpcsWarningOnNextLine ?>
<div aria-label="<?php echo $content; ?>">
<?php // @phpcsWarningOnNextLine ?>
    <?= $content; ?>
</div>

<?php
