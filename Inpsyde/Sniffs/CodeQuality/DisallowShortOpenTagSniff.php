<?php

/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP as Generic;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class DisallowShortOpenTagSniff extends Generic\DisallowShortOpenTagSniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
            T_INLINE_HTML,
        ];
    }
}
