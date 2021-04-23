<?php

$vendor = dirname(__DIR__) . '/vendor';
$phpcsSrc = "{$vendor}/squizlabs/php_codesniffer/src";

require_once dirname(__DIR__) . '/Inpsyde/PhpcsHelpers.php';
require_once "{$phpcsSrc}/Sniffs/Sniff.php";
require_once "{$phpcsSrc}/Files/File.php";
require_once "{$phpcsSrc}/Fixer.php";
require_once "{$phpcsSrc}/Util/Tokens.php";
require_once "{$phpcsSrc}/Standards/Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php";
require_once "{$phpcsSrc}/Standards/PSR12/Sniffs/Properties/ConstantVisibilitySniff.php";
require_once "{$phpcsSrc}/Config.php";
require_once "{$phpcsSrc}/Exceptions/RuntimeException.php";
