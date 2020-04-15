<?php

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

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class ForbiddenPublicPropertySniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     */
    public function process(File $file, $position)
    {
        if (!PhpcsHelpers::variableIsProperty($file, $position)) {
            return;
        }

        // Skip sniff classes, they have public properties for configuration (unfortunately)
        if ($this->isSniffClass($file, $position)) {
            return;
        }

        $scopeModifierToken = $this->propertyScopeModifier($file, $position);
        if ($scopeModifierToken['code'] === T_PUBLIC) {
            $file->addError(
                'Do not use public properties. Use method access instead.',
                $position,
                'Found'
            );
        }
    }

    private function isSniffClass(File $file, int $position): bool
    {
        $classNameTokenPosition = $file->findNext(
            T_STRING,
            (int)$file->findPrevious(T_CLASS, $position)
        );

        $classNameToken = $file->getTokens()[$classNameTokenPosition];

        if (substr($classNameToken['content'], -5, 5) === 'Sniff') {
            return true;
        }

        return false;
    }

    private function propertyScopeModifier(File $file, int $position): array
    {
        $scopeModifierPosition = $file->findPrevious(Tokens::$scopeModifiers, ($position - 1));

        return $file->getTokens()[$scopeModifierPosition];
    }
}
