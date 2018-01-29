<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\CodingStandard;

use PHP_CodeSniffer\Files\File;

/**
 * @package php-coding-standards
 * @license MIT
 */
class Helpers
{
    const CODE_TO_TYPE_MAP = [
        T_CONST => 'Constant',
        T_CLASS => 'Class',
        T_FUNCTION => 'Function',
        T_TRAIT => 'Trait',
    ];

    /**
     * @param File $file
     * @param int $position
     * @return mixed[]
     */
    public static function classPropertiesTokenIndexes(
        File $file,
        int $position
    ): array {
        $tokens = $file->getTokens();
        $token = $tokens[$position] ?? [];

        if (!array_key_exists('scope_opener', $token)
            || !array_key_exists('scope_closer', $token)
        ) {
            return [];
        }

        $propertyList = [];
        $pointer = (int)$token['scope_opener'];

        while ($pointer) {
            if (self::isProperty($file, $pointer)) {
                $propertyList[] = $pointer;
            }
            $pointer = (int)$file->findNext(
                T_VARIABLE,
                ($pointer + 1),
                $token['scope_closer']
            );
        }

        return $propertyList;
    }

    /**
     * @param File $file
     * @param int $variablePosition
     * @return bool
     */
    public static function isProperty(File $file, int $variablePosition): bool
    {
        $propertyPointer = $file->findPrevious(
            [T_STATIC, T_WHITESPACE, T_COMMENT],
            $variablePosition - 1,
            null,
            true
        );

        $propertyPointerToken = $file->getTokens()[$propertyPointer] ?? [];

        return in_array(
            ($propertyPointerToken['code'] ?? ''),
            [T_PRIVATE, T_PROTECTED, T_PUBLIC, T_VAR],
            true
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function tokenTypeName(File $file, int $position): string
    {
        $token = $file->getTokens()[$position];
        $tokenCode = $token['code'];
        if (isset(self::CODE_TO_TYPE_MAP[$tokenCode])) {
            return self::CODE_TO_TYPE_MAP[$tokenCode];
        }

        if ($token['code'] === T_VARIABLE) {
            if (self::isProperty($file, $position)) {
                return 'Property';
            }

            return 'Variable';
        }

        return '';
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function tokenName(File $file, int $position): string
    {
        $name = $file->getTokens()[$position]['content'] ?? '';

        if (strpos($name, '$') === 0) {
            return trim($name, '$');
        }

        $namePosition = $file->findNext(T_STRING, $position, $position + 3);

        return $file->getTokens()[$namePosition]['content'];
    }
}