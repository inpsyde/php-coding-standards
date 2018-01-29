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
final class NoSetterSniff implements Sniff
{
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
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    public function process(File $file, $position): void
    {
        $declarationName = $file->getDeclarationName($position);
        if ($declarationName === null) {
            return;
        }

        if ($this->methodNameStartsWithSet($declarationName)) {
            $file->addWarning(
                'Setters are discouraged. Use constructor injection and'
                . ' behavior naming instead, e.g. changeName() instead of setName().',
                $position,
                'NoSetter'
            );
        }
    }

    /**
     * @param string $methodName
     * @return bool
     */
    private function methodNameStartsWithSet(string $methodName): bool
    {
        return $methodName !== 'setUp'
            && preg_match('/^set[A-Z0-9]/', $methodName) === 1;
    }
}
