<?php

declare(strict_types=1);

# -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\CodingStandard\Tests;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class SniffMessages
{
    /**
     * @var array
     */
    private $warnings;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $messages;

    /**
     * @var bool
     */
    private $messagesContainTotal = false;

    /**
     * @param array $warnings
     * @param array $errors
     * @param array|null $messages
     */
    public function __construct(array $warnings, array $errors, array $messages = null)
    {
        if (is_null($messages)) {
            $messages = $errors + $warnings;
            $this->messagesContainTotal = true;
        }

        $this->warnings = $warnings;
        $this->errors = $errors;
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * @param int $line
     * @return string|null
     */
    public function messageIn(int $line)
    {
        return $this->messages[$line] ?? null;
    }

    /**
     * @return array
     */
    public function messageLines(): array
    {
        $messageLines = array_keys($this->messages);
        if ($this->messagesContainTotal) {
            return $messageLines;
        }

        return array_unique(array_merge($this->errorLines(), $this->warningLines(), $messageLines));
    }

    /**
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param int $line
     * @return string|null
     */
    public function errorIn(int $line)
    {
        return $this->errors[$line] ?? null;
    }

    /**
     * @return array
     */
    public function errorLines(): array
    {
        return array_keys($this->errors);
    }

    /**
     * @return array
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param int $line
     * @return string|null
     */
    public function warningIn(int $line)
    {
        return $this->warnings[$line] ?? null;
    }

    /**
     * @return array
     */
    public function warningLines(): array
    {
        return array_keys($this->warnings);
    }

    /**
     * @return int
     */
    public function total(): int
    {
        if ($this->messagesContainTotal) {
            return count($this->messages);
        }

        return count($this->messages) + count($this->errors) + count($this->warnings);
    }
}
