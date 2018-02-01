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

namespace Inpsyde\InpsydeCodingStandard\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FunctionLengthSniff implements Sniff
{
    /**
     * @var int
     */
    public $maxLength = 50;

    /**
     * @var true
     */
    public $ignoreDocBlocks = true;

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position)
    {
        $length = $this->getStructureLengthInLines($file, $position);

        if ($length > $this->maxLength) {
            $error = sprintf(
                'Your function is too long. Currently using %d lines. Can be up to %d lines.',
                $length,
                $this->maxLength
            );

            $file->addError($error, $position, 'TooLong');
        }
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public function getStructureLengthInLines(File $file, int $position): int
    {
        $tokens = $file->getTokens();
        $token = $tokens[$position] ?? [];

        if (!array_key_exists('scope_opener', $token)
            || !array_key_exists('scope_closer', $token)
        ) {
            return 0;
        }

        $opener = $token['scope_opener'];
        $closer = $token['scope_closer'];
        $length = $tokens[$closer]['line'] - $tokens[$opener]['line'];

        if (!$this->ignoreDocBlocks) {
            return $length;
        }

        $decrease = 0;
        for ($i = $opener + 1; $i < $closer; $i++) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_OPEN_TAG) {
                $openerLine = (int)$tokens[$i]['line'];
                $closer = $tokens[$i]['comment_closer'] ?? null;
                $decrease += is_numeric($closer)
                    ? (int)$tokens[$closer]['line'] - ($openerLine - 1)
                    : 1;
            }
        }

        return max(0, $length - $decrease);
    }
}
