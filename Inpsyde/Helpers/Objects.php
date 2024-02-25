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

namespace Inpsyde\CodingStandard\Helpers;

use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\Scopes;
use PHPCSUtils\Utils\UseStatements;

final class Objects
{
    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public static function countProperties(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        if (
            !in_array(
                $tokens[$position]['code'] ?? null,
                Collections::ooPropertyScopes(),
                true
            )
        ) {
            return 0;
        }

        [$start, $end] = Boundaries::objectBoundaries($file, $position);
        if (($start < 0) || ($end < 0)) {
            return 0;
        }


        $found = 0;

        $next = $start + 1;
        while ($next < $end) {
            [, $innerFunctionEnd] = Boundaries::functionBoundaries($file, $next);
            if ($innerFunctionEnd > 0) {
                $next = $innerFunctionEnd + 1;
                continue;
            }

            if (
                (($tokens[$next]['code'] ?? '') === T_VARIABLE)
                && Scopes::isOOProperty($file, $next)
            ) {
                $found++;
            }

            $next++;
        }

        return $found;
    }

    /**
     * @param File $file
     * @param int $position
     * @return array<string, string>
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public static function findAllImportUses(File $file, int $position): array
    {
        // phpcs:enable Generic.Metrics.CyclomaticComplexity
        $usePositions = [];
        $nextUse = $file->findPrevious(T_NAMESPACE, $position - 1);
        ($nextUse === false) and $nextUse = 0;

        while (true) {
            $nextUse = $file->findNext(T_USE, $nextUse + 1, $position - 1);
            if ($nextUse === false) {
                break;
            }
            if (!UseStatements::isImportUse($file, $nextUse)) {
                continue;
            }
            $usePositions[] = $nextUse;
        }

        if (!$usePositions) {
            return [];
        }

        $tokens = $file->getTokens();
        $uses = [];
        $useNameEnd = $file->findEndOfStatement(end($usePositions));
        foreach ($usePositions as $i => $usePosition) {
            $end = ($i === (count($usePositions)) - 1) ? $useNameEnd : $usePositions[$i + 1];
            $asPos = $file->findNext(T_AS, $usePosition + 1, $end, false, null, true);
            $useName = Misc::tokensSubsetToString(
                $usePosition + 1,
                (($asPos !== false) ? $asPos : $end) - 1,
                $file,
                [T_STRING, T_NS_SEPARATOR]
            );
            $useName = trim($useName, '\\');
            $useNameParts = explode('\\', $useName);
            $key = end($useNameParts);
            if ($asPos !== false) {
                $keyPos = $file->findNext(T_STRING, $asPos + 1, null, false, null, true);
                /** @var string $key */
                $key = $tokens[$keyPos]['content'] ?? '';
            }
            $uses[$key] = $useName;
        }

        return $uses;
    }

    /**
     * @param File $file
     * @param int $position
     * @return list<string>|null
     */
    public static function allInterfacesFullyQualifiedNames(File $file, int $position): ?array
    {
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'] ?? null;
        if (!in_array($code, Collections::ooCanImplement(), true)) {
            return null;
        }

        $implementsPos = $file->findNext(T_IMPLEMENTS, $position, null, false, null, true);
        if ($implementsPos === false) {
            return null;
        }

        $namesEnd = $file->findNext(
            [T_OPEN_CURLY_BRACKET, T_EXTENDS],
            $position,
            null,
            false,
            null,
            true
        );

        if ($namesEnd === false) {
            return null;
        }

        $uses = static::findAllImportUses($file, $position - 1);
        /** @var non-empty-list<string>|false $names */
        $names = ObjectDeclarations::findImplementedInterfaceNames($file, $position);
        if (!$names) {
            return [];
        }

        $fqns = [];
        foreach ($names as $name) {
            if (strpos($name, '\\') === 0) {
                $fqns[] = $name;
                continue;
            }
            $parts = explode('\\', $name);
            $first = $parts[0];
            if (isset($uses[$first])) {
                array_shift($parts);
                $fqns[] = rtrim('\\' . $uses[$first] . '\\' . implode('\\', $parts), '\\');
                continue;
            }
            $namespace = Namespaces::determineNamespace($file, $position);
            $fqns[] = $namespace ? "\\{$namespace}\\{$name}" : "\\{$name}";
        }

        return $fqns;
    }
}
