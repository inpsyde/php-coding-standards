<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file contains code from "phpcs-calisthenics-rules" repository
 * found at https://github.com/object-calisthenics
 * Copyright (c) 2014 Doctrine Project
 * released under MIT license.
 */

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class NoElseSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [T_ELSE];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position)
    {
        $file->addWarning(
            'Do not use "else". Prefer early return statement instead.',
            $position,
            'ElseFound'
        );
    }
}
