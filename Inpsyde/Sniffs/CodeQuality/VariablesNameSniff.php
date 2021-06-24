<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class VariablesNameSniff implements Sniff
{
    const GLOBALS = [
        '$_GET',
        '$_POST',
        '$_REQUEST',
        '$_SERVER',
        '$_COOKIE',
        '$_FILES',
        '$_SESSION',
        '$_ENV',
        '$GLOBALS',
    ];

    const WP_GLOBALS = [
        '$current_user',
        '$is_iphone',
        '$is_chrome',
        '$is_safari',
        '$is_NS4',
        '$is_opera',
        '$is_macIE',
        '$is_winIE',
        '$is_gecko',
        '$is_lynx',
        '$is_IE',
        '$is_edge',
        '$is_apache',
        '$is_IIS',
        '$is_iis7',
        '$tinymce_version',
        '$manifest_version',
        '$required_php_version',
        '$required_mysql_version',
        '$super_admins',
        '$interim_login',
    ];

    /**
     * @var mixed
     */
    public $checkType = 'camelCase';

    /**
     * @var mixed
     */
    public $ignoredNames = [];

    /**
     * @var mixed
     */
    public $ignoreLocalVars = false;

    /**
     * @var mixed
     */
    public $ignoreProperties = false;

    /**
     * @return array<int>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return [T_VARIABLE];
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

        $ignored = $this->allIgnored();

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $name = (string)$tokens[$position]['content'];

        if (
            in_array($name, $ignored, true)
            || strpos($name, '$wp_') === 0
            || strpos($name, '$_wp_') === 0
        ) {
            return;
        }

        $isCamelCase = $this->checkType() === 'camelCase';

        $valid = $isCamelCase ? $this->checkCamelCase($name) : $this->checkSnakeCase($name);
        if ($valid) {
            return;
        }

        $isProperty = PhpcsHelpers::variableIsProperty($file, $position);

        if (
            ($isProperty && $this->arePropertiesIgnored())
            || (!$isProperty && $this->areVariablesIgnored())
        ) {
            return;
        }

        $file->addWarning(
            sprintf(
                '"%s" should be used for variable names.',
                $isCamelCase ? '$camelCase' : '$snake_case'
            ),
            $position,
            $isCamelCase ? 'SnakeCaseVar' : 'CamelCaseVar'
        );
    }

    /**
     * @return string
     */
    private function checkType(): string
    {
        if (!is_string($this->checkType)) {
            return 'camelCase';
        }

        $type = strtolower(trim($this->checkType));
        if (in_array($type, ['camelcase', 'snake_case'], true)) {
            return $type === 'camelcase' ? 'camelCase' : 'snake_case';
        }

        return 'camelCase';
    }

    /**
     * @return bool
     */
    private function arePropertiesIgnored(): bool
    {
        return (bool)filter_var($this->ignoreProperties, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     */
    private function areVariablesIgnored(): bool
    {
        return (bool)filter_var($this->ignoreLocalVars, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkCamelCase(string $name): bool
    {
        return preg_match('~^\$[a-z]+(?:[a-zA-Z0-9]+)?$~', $name)
            && !preg_match('~[A-Z]{2,}~', $name);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkSnakeCase(string $name): bool
    {
        return (bool)preg_match('~^\$[a-z]+(?:[a-z0-9_]+)?$~', $name);
    }

    /**
     * @return array
     *
     * psalm-assert array<string> $this->ignoredNames
     */
    private function allIgnored(): array
    {
        if (is_string($this->ignoredNames)) {
            $this->ignoredNames = explode(',', $this->ignoredNames);
        }

        if (!is_array($this->ignoredNames)) {
            $this->ignoredNames = [];
        }

        $ignored = $this->ignoredNames;

        /** @var array<string> $normalized */
        $normalized = [];
        foreach ($ignored as $name) {
            if (is_string($name)) {
                $normalized[] = '$' . ltrim(trim($name), '$');
            }
        }

        $this->ignoredNames = $normalized;

        return array_merge($normalized, self::GLOBALS, self::WP_GLOBALS);
    }
}
