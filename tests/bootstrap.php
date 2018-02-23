<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the Wonolog package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

putenv('SNIFFS_PATH=' . realpath(dirname(__DIR__) . '/Inpsyde/Sniffs'));
putenv('SNIFFS_NAMESPACE=' . 'Inpsyde\\Sniffs');
putenv('FIXTURES_PATH=' . realpath(__DIR__ . '/fixtures'));

$vendor = dirname(__DIR__) . '/vendor/';
if (!realpath($vendor)) {
    die('Please install via Composer before running tests.');
}

require_once($vendor . 'autoload.php');
require_once($vendor . 'squizlabs/php_codesniffer/tests/bootstrap.php');
require_once(dirname(__DIR__) . '/Inpsyde/PhpcsHelpers.php');
unset($vendor);
