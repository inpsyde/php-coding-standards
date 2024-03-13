<?php

declare(strict_types=1);

$vendorDir = dirname(__DIR__) . '/vendor';

require_once "{$vendorDir}/squizlabs/php_codesniffer/autoload.php";

require_once "{$vendorDir}/squizlabs/php_codesniffer/tests/bootstrap.php";
require_once "{$vendorDir}/wp-coding-standards/wpcs/WordPress/Sniff.php";
require_once "{$vendorDir}/wp-coding-standards/wpcs/WordPress/AbstractArrayAssignmentRestrictionsSniff.php";
require_once "{$vendorDir}/wp-coding-standards/wpcs/WordPress/AbstractFunctionRestrictionsSniff.php";
require_once "{$vendorDir}/wp-coding-standards/wpcs/WordPress/AbstractFunctionParameterSniff.php";
require_once "{$vendorDir}/wp-coding-standards/wpcs/WordPress/AbstractClassRestrictionsSniff.php";
foreach (glob("{$vendorDir}/wp-coding-standards/wpcs/WordPress/Helpers/*.php") as $file) {
    require_once $file;
}
