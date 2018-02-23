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

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
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
    public function register()
    {
        return [T_CLASS, T_TRAIT];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position)
    {
        $count = count(PhpcsHelpers::classPropertiesTokenIndexes($file, $position));

        if ($count > $this->maxCount) {
            $tokenType = $file->getTokens()[$position]['content'];

            $message = sprintf(
                '"%s" has too many properties: %d. Can be up to %d properties.',
                $tokenType,
                $count,
                $this->maxCount
            );

            $file->addWarning($message, $position, 'TooMuchProperties');
        }
    }
}
