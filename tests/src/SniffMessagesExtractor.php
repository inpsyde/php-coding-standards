<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\InpsydeCodingStandard\Tests;

use PHP_CodeSniffer\Files\File;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class SniffMessagesExtractor
{
    /**
     * @var File
     */
    private $file;

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
        list($warnings, $errors) = $this->normalize(
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
