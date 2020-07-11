<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class StaticClosureSniff implements Sniff
{
    /**
     * @return array<string>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return [T_CLOSURE];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function process(File $file, $position)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        list($functionStart, $functionEnd) = PhpcsHelpers::functionBoundaries($file, $position);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $isStatic = $file->findPrevious(T_STATIC, $position, $position - 3, false, null, true);
        if ($isStatic) {
            return;
        }

        $thisFound = false;
        $i = $functionStart + 1;

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        while (!$thisFound && ($i < $functionEnd)) {
            $token = $tokens[$i];
            $thisFound = ($token['code'] === T_VARIABLE) && ($token['content'] === '$this');
            $i++;
        }

        if ($thisFound) {
            return;
        }

        $boundDoc = PhpcsHelpers::functionDocBlockTag('@bound', $file, $position);
        if ($boundDoc) {
            return;
        }

        $varDoc = PhpcsHelpers::functionDocBlockTag('@var', $file, $position);
        foreach ($varDoc as $content) {
            if (preg_match('~(?:^|\s+)\$this(?:$|\s+)~', $content)) {
                return;
            }
        }

        $line = (int)$tokens[$position]['line'];
        $message = sprintf('Closure found at line %d could be static.', $line);

        if ($file->addFixableWarning($message, $position, 'PossiblyStaticClosure')) {
            $this->fix($position, $file);
        }
    }

    /**
     * @param int $position
     * @param File $file
     * @return void
     */
    private function fix(int $position, File $file)
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($position, 'static function');

        $fixer->endChangeset();
    }
}
