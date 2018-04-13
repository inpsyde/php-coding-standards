<?php // phpcs:disable

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
     * @var bool
     */
    public $checkType = 'camelCase';

    /**
     * @var string[]
     */
    public $ignoredNames = [];

    /**
     * @var bool
     */
    public $ignoreLocalVars = false;

    /**
     * @var bool
     */
    public $ignoreProperties = false;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_VARIABLE
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $ignored = $this->allIgnored();
        $name = $phpcsFile->getTokens()[$stackPtr]['content'];

        if (in_array($name, $ignored, true) || strpos($name, '$wp_') === 0) {
            return;
        }

        $isCamelCase = $this->checkType() === 'camelCase';

        $valid = $isCamelCase ? $this->checkCamelCase($name) : $this->checkSnakeCase($name);
        if ($valid) {
            return;
        }

        $isProperty = PhpcsHelpers::variableIsProperty($phpcsFile, $stackPtr);

        if (($isProperty && $this->arePropertiesIgnored())
            || (!$isProperty && $this->areVariablesIgnored())
        ) {
            return;
        }

        $phpcsFile->addWarning(
            sprintf(
                '"%s" should be used for variable names.',
                $isCamelCase ? '$camelCase' : '$snake_case'
            ),
            $stackPtr,
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
       return (bool) filter_var($this->ignoreProperties, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     */
    private function areVariablesIgnored(): bool
    {
        return (bool) filter_var($this->ignoreLocalVars, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkCamelCase(string $name): bool
    {
        return preg_match('~^\$[a-z]+(?:[a-zA-Z0-9]+)?$~', $name)
            && ! preg_match('~[A-Z]{2,}~', $name);
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
     */
    private function allIgnored(): array
    {
        if (is_string($this->ignoredNames)) {
            $this->ignoredNames = explode(',', $this->ignoredNames);
        }

        if (!is_array($this->ignoredNames)) {
            $this->ignoredNames = [];
        }

        $normalized = [];
        foreach ($this->ignoredNames as $name) {
            if (is_string($name)) {
                $normalized[] = '$' . ltrim(trim($name), '$');
            }
        }

        $this->ignoredNames = $normalized;

        return array_merge($normalized, self::GLOBALS, self::WP_GLOBALS);
    }
}
