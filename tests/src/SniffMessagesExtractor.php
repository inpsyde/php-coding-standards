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

use PHP_CodeSniffer\Files\File;

class SniffMessagesExtractor
{
    private File $file;

    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return SniffMessages
     */
    public function extractMessages(): SniffMessages
    {
        $this->file->process();
        [$warnings, $errors] = $this->normalize(
            $this->file->getWarnings(),
            $this->file->getErrors()
        );

        return new SniffMessages($warnings, $errors);
    }

    /**
     * @param array $actualWarnings
     * @param array $actualErrors
     * @return array
     */
    private function normalize(array $actualWarnings, array $actualErrors): array
    {
        $normalized = [[], []];

        foreach ($actualWarnings as $line => $lineMessages) {
            $normalized[0] += $this->normalizeLineMessages($line, $lineMessages);
        }

        foreach ($actualErrors as $line => $lineMessages) {
            $normalized[1] += $this->normalizeLineMessages($line, $lineMessages);
        }

        return $normalized;
    }

    /**
     * @param int $line
     * @param array $lineMessages
     * @return array
     */
    private function normalizeLineMessages(int $line, array $lineMessages): array
    {
        $normalized = [];
        foreach ($lineMessages as $messages) {
            $message = array_shift($messages);
            $sourceParts = explode('.', ($message['source'] ?? ''));
            $normalized[$line] = end($sourceParts);
        }

        return $normalized;
    }
}
