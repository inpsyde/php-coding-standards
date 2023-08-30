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

use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

class DisableCallUserFuncSniff extends AbstractFunctionRestrictionsSniff
{
    /**
     * @return array<string, array<string, string|array>>
     *
     * phpcs:disable Inpsyde.CodeQuality.NoAccessors
     */
    public function getGroups(): array
    {
        // phpcs:enable Inpsyde.CodeQuality.NoAccessors
        return [
            'call_user_func' => [
                'type' => 'error',
                'message' => 'Usage of %s() is forbidden.',
                'functions' => [
                    'call_user_func',
                    'call_user_func_array',
                    'suca',
                ],
            ],
        ];
    }
}
