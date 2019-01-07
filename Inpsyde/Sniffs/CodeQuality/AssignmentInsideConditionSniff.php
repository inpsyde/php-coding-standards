<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class AssignmentInsideConditionSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [T_IF, T_ELSEIF];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position)
    {
        $tokens = $file->getTokens();
        $ifOpenerPosition = $tokens[$position]['parenthesis_opener'] ?? -1;
        $ifCloserPosition = $tokens[$position]['parenthesis_closer'] ?? -1;

        if ($ifOpenerPosition < 0 || $ifCloserPosition <= ($ifOpenerPosition + 1)) {
            return;
        }

        $insideIfAssignmentPositions = $this->findAssignmentPositions(
            $ifOpenerPosition,
            $ifCloserPosition,
            $file
        );

        if (!$insideIfAssignmentPositions) {
            return;
        }

        foreach ($insideIfAssignmentPositions as $insideIfAssignmentPosition) {
            if ($this->isAssignmentWrapped($insideIfAssignmentPosition, $ifOpenerPosition, $file)) {
                continue;
            }

            $file->addWarning(
                'Please avoid assignments inside conditions, or at least wrap them in parenthesis.',
                $position,
                'Found'
            );
        }
    }

    /**
     * @param int $ifOpenerPosition
     * @param int $ifCloserPosition
     * @param File $file
     * @return int[]
     */
    private function findAssignmentPositions(
        int $ifOpenerPosition,
        int $ifCloserPosition,
        File $file
    ): array {

        $assignmentTokens = PhpcsHelpers::filterTokensByType(
            $ifOpenerPosition + 1,
            $ifCloserPosition - 1,
            $file,
            ...array_values(Tokens::$assignmentTokens)
        );

        if (!$assignmentTokens) {
            return [];
        }

        return array_keys($assignmentTokens);
    }

    /**
     * @param int $insideIfAssignmentPosition
     * @param int $ifOpenerPosition
     * @param File $file
     * @return bool
     */
    private function isAssignmentWrapped(
        int $insideIfAssignmentPosition,
        int $ifOpenerPosition,
        File $file
    ): bool {

        $insideIfOpenParenthesisPosition = $file->findPrevious(
            T_OPEN_PARENTHESIS,
            $insideIfAssignmentPosition - 1,
            $ifOpenerPosition,
            false,
            null,
            true
        );

        if ($insideIfOpenParenthesisPosition <= $ifOpenerPosition) {
            return false;
        }

        $openParenthesisToken = $file->getTokens()[$insideIfOpenParenthesisPosition];
        $insideIfCloserParenthesisPosition = $openParenthesisToken['parenthesis_closer'] ?? -1;

        if ($insideIfCloserParenthesisPosition < ($insideIfAssignmentPosition + 1)) {
            return false;
        }

        $parenthesisInBetween = PhpcsHelpers::filterTokensByType(
            $insideIfOpenParenthesisPosition + 1,
            $insideIfCloserParenthesisPosition - 1,
            $file,
            T_OPEN_PARENTHESIS,
            T_CLOSE_PARENTHESIS
        );

        return !$parenthesisInBetween;
    }
}
