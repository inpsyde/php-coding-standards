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

namespace Inpsyde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

final class WpHooks
{
    /**
     * @param File $file
     * @param int $position
     * @param bool $lookForFilters
     * @param bool $lookForActions
     * @return bool
     */
    public static function isHookClosure(
        File $file,
        int $position,
        bool $lookForFilters = true,
        bool $lookForActions = true
    ): bool {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (!in_array(($tokens[$position]['code'] ?? ''), [T_CLOSURE, T_FN], true)) {
            return false;
        }

        $empty = Tokens::$emptyTokens;

        $exclude = $empty;
        $exclude[] = T_STATIC;
        $commaPos = $file->findPrevious($exclude, $position - 1, null, true, null, true);
        if (!$commaPos || ($tokens[$commaPos]['code'] ?? '') !== T_COMMA) {
            return false;
        }

        $openType = [T_OPEN_PARENTHESIS];
        $openCallPos = $file->findPrevious($openType, $commaPos - 2, null, false, null, true);
        if (!$openCallPos) {
            return false;
        }

        $functionCallPos = $file->findPrevious($empty, $openCallPos - 1, null, true, null, true);
        if (!$functionCallPos || $tokens[$functionCallPos]['code'] !== T_STRING) {
            return false;
        }

        $actions = [];
        $lookForFilters and $actions[] = 'add_filter';
        $lookForActions and $actions[] = 'add_action';

        return in_array($tokens[$functionCallPos]['content'] ?? '', $actions, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isHookFunction(File $file, int $position): bool
    {
        return (bool)FunctionDocBlock::tag('@wp-hook', $file, $position);
    }
}
