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

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class ElementNameMinimalLengthSniff implements Sniff
{
    /**
     * @var int
     */
    public $minLength = 3;

    /**
     * @var string[]
     */
    public $allowedShortNames = [
        'as',
        'at',
        'be',
        'db',
        'do',
        'ex',
        'go',
        'i',
        'id',
        'if',
        'in',
        'io',
        'is',
        'it',
        'my',
        'no',
        'of',
        'ok',
        'on',
        'or',
        'to',
        'up',
        'wp',
    ];

    /**
     * @var string[]
     */
    public $additionalAllowedNames = [];

    /**
     * @return int[]
     */
    public function register()
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE, T_CONST, T_FUNCTION, T_VARIABLE];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     */
    public function process(File $file, $position)
    {
        $elementName = PhpcsHelpers::tokenName($file, $position);
        $elementNameLength = mb_strlen($elementName);

        if ($this->shouldBeSkipped($elementNameLength, $elementName)) {
            return;
        }

        $typeName = PhpcsHelpers::tokenTypeName($file, $position);
        $message = sprintf(
            '%s name "%s" is only %d chars long. Must be at least %d.',
            $typeName,
            $elementName,
            $elementNameLength,
            $this->minLength
        );

        $file->addError($message, $position, 'TooShort');
    }

    private function shouldBeSkipped(int $elementNameLength, string $elementName): bool
    {
        return ($elementNameLength >= $this->minLength) || $this->isShortNameAllowed($elementName);
    }

    private function isShortNameAllowed(string $variableName): bool
    {
        $target = strtolower($variableName);

        foreach ($this->allowedShortNames as $allowed) {
            if (strtolower($allowed) === $target) {
                return true;
            }
        }

        foreach ($this->additionalAllowedNames as $allowed) {
            if (strtolower($allowed) === $target) {
                return true;
            }
        }

        return false;
    }
}
