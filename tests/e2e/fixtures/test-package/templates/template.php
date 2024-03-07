<?php

declare(strict_types=1);

$value = rand(0, 1);
$successMessage = 'Success!';
$errorMessage = 'Error!' ?>

<?php if ($value > 0.5) : ?>
    <?= esc_html($successMessage) ?>
<?php else : ?>
    <?= $errorMessage; ?>
<?php endif;

call_user_func('strtolower', 'foo');