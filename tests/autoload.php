<?php

/*
 * This file is part of the "php-coding-standards" package.
 *
 * Copyright (c) 2023 Inpsyde GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
