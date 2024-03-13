<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP as Generic;

class DisallowShortOpenTagSniff extends Generic\DisallowShortOpenTagSniff
{
    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_OPEN_TAG, T_INLINE_HTML];
    }
}
