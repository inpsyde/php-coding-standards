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

namespace Inpsyde\CodingStandard\Tests;

final class SniffMessages
{
    private array $warnings;
    private array $errors;
    private array $messages;
    private bool $messagesContainTotal = false;

    /**
     * @param array $warnings
     * @param array $errors
     * @param array|null $messages
     */
    public function __construct(array $warnings, array $errors, ?array $messages = null)
    {
        if ($messages === null) {
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
    public function messageIn(int $line): ?string
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
    public function errorIn(int $line): ?string
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
    public function warningIn(int $line): ?string
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
