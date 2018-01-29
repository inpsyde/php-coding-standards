<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\CodingStandard\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license MIT
 */
final class NoElseSniff implements Sniff
{
    /**
     * @var string
     */
    const ERROR_MESSAGE = 'Do not use "else". Prefer early return statement instead.';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_ELSE];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position): void
    {
        $file->addWarning(self::ERROR_MESSAGE, $position, 'NoElse');
    }
}
