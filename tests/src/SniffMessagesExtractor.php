<?php

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
