<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file contains code from "phpcs-neutron-standard" repository
 * found at https://github.com/Automattic/phpcs-neutron-standard
 * Copyright (c) Automattic Inc.
 * released under MIT license.
 */

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ArgumentTypeDeclarationSniff implements Sniff
{
    const TYPE_CODES = [
        T_STRING,
        T_ARRAY_HINT,
        T_CALLABLE,
        T_SELF,
    ];

    const METHODS_WHITELIST = [
        'unserialize',
        'seek',
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_FUNCTION, T_CLOSURE];
    }

    /**
     * @inheritdoc
     */
    public function process(File $file, $position)
    {
        if (PhpcsHelpers::functionIsArrayAccess($file, $position)
            || PhpcsHelpers::isHookClosure($file, $position)
            || PhpcsHelpers::isHookFunction($file, $position)
            || (
                PhpcsHelpers::functionIsMethod($file, $position)
                && in_array($file->getDeclarationName($position), self::METHODS_WHITELIST, true)
            )
        ) {
            return;
        }

        $tokens = $file->getTokens();

        $paramsStart = $tokens[$position]['parenthesis_opener'] ?? 0;
        $paramsEnd = $tokens[$position]['parenthesis_closer'] ?? 0;

        if (!$paramsStart || !$paramsEnd || $paramsStart >= ($paramsEnd - 1)) {
            return;
        }

        $variables = PhpcsHelpers::filterTokensByType($paramsStart, $paramsEnd, $file, T_VARIABLE);

        foreach (array_keys($variables) as $varPosition) {
            $typePosition = $file->findPrevious(
                [T_WHITESPACE, T_ELLIPSIS, T_BITWISE_AND],
                $varPosition - 1,
                $paramsStart + 1,
                true
            );

            $type = $tokens[$typePosition] ?? null;
            if ($type && !in_array($type['code'], self::TYPE_CODES, true)) {
                $file->addWarning('Argument type is missing', $position, 'NoArgumentType');
            }
        }
    }
}
