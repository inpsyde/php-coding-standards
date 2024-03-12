<?php

declare(strict_types=1);

$value = rand(0, 1);

$successMessage = 'Success!';
$errorMessage = 'Error!';

if ($value > 0.5) {
    echo esc_html($successMessage);
} else {
    echo $errorMessage;
}
