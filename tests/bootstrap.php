<?php

/**
 * This file is part of the Wonolog package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

$testsDir = str_replace('\\', '/', __DIR__);
$libDir = dirname($testsDir);
$vendorDir = "{$libDir}/vendor";
$autoload = "{$vendorDir}/autoload.php";

if (!is_file($autoload)) {
    die('Please install via Composer before running tests.');
}

putenv("SNIFFS_PATH={$libDir}/Inpsyde/Sniffs");
putenv('SNIFFS_NAMESPACE=Inpsyde\\Sniffs');
putenv("FIXTURES_PATH={$testsDir}/fixtures");

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', $autoload);
    require_once $autoload;
}

require_once "{$testsDir}/autoload.php";

unset($libDir, $testsDir, $vendorDir, $autoload);

error_reporting(E_ALL);
