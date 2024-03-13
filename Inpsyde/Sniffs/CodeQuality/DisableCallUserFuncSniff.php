<?php

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
                ],
            ],
        ];
    }
}
