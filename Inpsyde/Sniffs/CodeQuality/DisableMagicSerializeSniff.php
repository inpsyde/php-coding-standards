<?php

/**
 * This file is part of the "php-coding-standards" package.
 *
 * Copyright (C) 2023 Inpsyde GmbH
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

class DisableMagicSerializeSniff implements Sniff
{
    /** @var list<string>  */
    public array $disabledFunctions = [
        '__serialize',
        '__sleep',
        '__unserialize',
        '__wakeup',
    ];

    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        if (!Scopes::isOOMethod($phpcsFile, $stackPtr)) {
            return;
        }

        $name = FunctionDeclarations::getName($phpcsFile, $stackPtr);
        if (in_array($name, $this->disabledFunctions, true)) {
            $phpcsFile->addError(
                sprintf(
                    'The method "%s" is forbidden, please use Serializable interface.',
                    $name
                ),
                $stackPtr,
                'Found'
            );
        }
    }
}
