<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Helpers;

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
        if (($commaPos === false) || ($tokens[$commaPos]['code'] ?? '') !== T_COMMA) {
            return false;
        }

        $openType = [T_OPEN_PARENTHESIS];
        $openCallPos = $file->findPrevious($openType, $commaPos - 2, null, false, null, true);
        if ($openCallPos === false) {
            return false;
        }

        $functionCallPos = $file->findPrevious($empty, $openCallPos - 1, null, true, null, true);
        if (($functionCallPos === false) || $tokens[$functionCallPos]['code'] !== T_STRING) {
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
