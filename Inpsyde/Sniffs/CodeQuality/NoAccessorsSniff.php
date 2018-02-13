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

namespace Inpsyde\InpsydeCodingStandard\Sniffs\CodeQuality;

use Inpsyde\InpsydeCodingStandard\Helpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class NoAccessorsSniff implements Sniff
{
    const ALLOWED_NAMES = [
        'getIterator',
        'getInnerIterator',
        'getChildren',
        'setUp',
    ];

    public $skipForFunctions = true;

    /**
     * @return int[]
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $file
     * @param int $position
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    public function process(File $file, $position)
    {
        if ($this->skipForFunctions && !Helpers::functionIsMethod($file, $position)) {
            return;
        }

        $functionName = $file->getDeclarationName($position);

        if (!$functionName || in_array($functionName, self::ALLOWED_NAMES, true)) {
            return;
        }

        preg_match('/^(set|get)[_A-Z0-9]+/', $functionName, $matches);
        if (!$matches) {
            return;
        }

        if ($matches[1] === 'set') {
            $file->addWarning(
                'Setters are discouraged. Try to use immutable objects, constructor injection '
                . 'and for objects that really needs changing state try behavior naming instead, '
                . 'e.g. changeName() instead of setName().',
                $position,
                'NoSetter'
            );

            return;
        }

        $file->addWarning(
            'Getters are discouraged. "Tell Don\'t Ask" principle should be applied if possible, '
            . 'and if getters are really needed consider naming methods after properties, '
            . 'e.g. name() instead of getName().',
            $position,
            'NoGetter'
        );
    }
}
