<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class PropertyPerClassLimitSniff implements Sniff
{
    /**
     * @var mixed
     */
    public $maxCount = 10;

    /**
     * @return array<int|string>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return array_values(Tokens::$ooScopeTokens);
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

        is_numeric($this->maxCount) or $this->maxCount = 10;
        $this->maxCount = (int)$this->maxCount;

        $count = count(PhpcsHelpers::allPropertiesTokenPositions($file, $position));
        if ($count <= $this->maxCount) {
            return;
        }

        $message = sprintf(
            '"%s" has too many properties: %d. Can be up to %d properties.',
            PhpcsHelpers::tokenTypeName($file, $position),
            $count,
            $this->maxCount
        );

        $file->addWarning($message, $position, 'TooManyProperties');
    }
}
