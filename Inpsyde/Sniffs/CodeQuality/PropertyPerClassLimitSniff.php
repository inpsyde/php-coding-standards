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

use Inpsyde\CodingStandard\Helpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license MIT
 */
final class PropertyPerClassLimitSniff implements Sniff
{
    /**
     * @var int
     */
    public $maxCount = 10;

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLASS, T_TRAIT];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position): void
    {
        $count = count(Helpers::classPropertiesTokenIndexes($file, $position));

        if ($count > $this->maxCount) {
            $tokenType = $file->getTokens()[$position]['content'];

            $message = sprintf(
                '"%s" has too many properties: %d. Can be up to %d properties.',
                $tokenType,
                $count,
                $this->maxCount
            );

            $file->addWarning($message, $position, 'PropertyPerClassLimit');
        }
    }
}
