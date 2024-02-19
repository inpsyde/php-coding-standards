# Inpsyde PHP Coding Standards

> PHP 7+ coding standards for Inpsyde WordPress projects.

![PHP Quality Assurance](https://github.com/inpsyde/php-coding-standards/workflows/PHP%20Quality%20Assurance/badge.svg)

-------------

# Installation

The code styles are enforced via the popular [`php_codesniffer`](https://packagist.org/packages/squizlabs/php_codesniffer)
and can be installed via Composer by the name **`inpsyde/php-coding-standards`**.

It means they can be installed by adding the entry to composer.json `require-dev`:

```json
{
    "require-dev": {
        "inpsyde/php-coding-standards": "^1@dev"
    }
}
```

(the `@dev` suffix can be removed if the project [`minimum-stability`](https://getcomposer.org/doc/04-schema.md#minimum-stability) is `"dev"`, or as soon as the stable v1.0.0 of the standards will be released.)


or via command line with: 

```shell
$ composer require inpsyde/php-coding-standards --dev
```

-------------

# Usage

## Basic usage

When the package is installed via Composer, and dependencies are updated, everything is
ready and the coding standards can be checked via:

```shell
$ vendor/bin/phpcs --standard="Inpsyde" <path>
```

Where `<path>` is at least one file or directory to check, e.g.:

```shell
$ vendor/bin/phpcs --standard="Inpsyde" ./src/ ./my-plugin.php
```

On  Windows it would be something like:

```shell
$ ./vendor/bin/phpcs.bat --standard="Inpsyde" ./src/ ./my-plugin.php
```

There are many options that can be used to customise the behavior of the command, to get documentation use:

```shell
$ vendor/bin/phpcs --help
```


## Configuration File

To do not have to pass all the arguments to the command line, and to also be able to do
customization it is also possible to create a `phpcs.xml.dist` file that contains something like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

    <description>My Project coding standard.</description>

    <file>./src</file>
    <file>./tests/src</file>

    <arg value="sp"/>
    <arg name="colors"/>

    <config name="testVersion" value="7.2-"/>
    <config name="text_domain" value="my-project"/>
    
    <rule ref="Inpsyde">
        <exclude name="WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize" />
    </rule>
    
    <rule ref="Inpsyde.CodeQuality.Psr4">
        <properties>
            <property
                name="psr4"
                type="array"
                value="Inpsyde\MyProject=>src,Inpsyde\MyProject\Tests=>tests/src|tests/unit"/>
        </properties>
    </rule>
    
    <rule ref="Inpsyde.CodeQuality.ElementNameMinimalLength">
        <properties>
            <property name="additionalAllowedNames" type="array" value="c,me,my" />
        </properties>
    </rule>

</ruleset>
```

Such a configuration allows to run the code style check with only:

```shell
$ vendor/bin/phpcs
```

Moreover, thanks to the `text_domain` setting, Code Sniffer will also check that all WP
internationalization functions are called with the proper text domain.

-------------

# Included rules

## PSR-1, PSR-2, PSR-12

See https://www.php-fig.org/psr/psr-1, https://www.php-fig.org/psr/psr-2, https://www.php-fig.org/psr/psr-12


## Neutron Standard

See https://github.com/Automattic/phpcs-neutron-standard

Almost all Neutron Standard rules are included.


## WordPress Coding Standard

To ensure code quality, and compatibility with VIP, several WordPress Coding Standard rules have been
"cherry picked" from WP coding standards.

See https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.

## PHPCompatibility

See https://github.com/wimg/PHPCompatibility.

It allows to analyse code for compatibility with higher and lower versions of PHP.
The default target version is PHP 7.0+.

Target version can be changed via custom `phpcs.xml`.


## Generic Rules

Some rules are also included from PHPCS itself.
Those rules fall in the "Generic", "Squiz" and "PEAR" namespace.
Some of them are included by other styles, mainly by PSR-1 and PSR-2.


## Custom Rules

Some custom rules are also in use. They are:

| Sniff name | Description | Has Config | Auto-Fixable |
|:-----------|:------------|:----------:|:------------:|
| `ArgumentTypeDeclaration`|Enforce argument type declaration.|||
| `ConstantVisibility`|Force use of visibility for constants.|||
| `DisallowShortOpenTag`|Disallow short open PHP tag (short echo tag allowed).|||
| `ElementNameMinimalLength`|Use minimum 3 chars for names (with a few exclusions)|✓||
| `ForbiddenPublicProperty`|No public class properties|||
| `FunctionBodyStart`|Handle blank line at start of function body.||✓|
| `FunctionLength`|Max 50 lines per function/method, excluding blank lines and comments-only lines.|✓||
| `HookClosureReturn`|Ensure that actions callbacks do not return anything, while filter callbacks return something.|||
| `LineLength`|Max 100 chars per line|✓||
| `NestingLevel`|Max indent level of 3 inside functions|✓||
| `NoAccessors`|Discourage usage of getters and setters.|||
| `NoElse`|Discourage usage of `else`.|||
| `NoTopLevelDefine`|Discourage usage of `define` where `const` is preferable.|||
| `PropertyPerClassLimit`|Discourage usage of more than 10 properties per class.|✓||
| `Psr4`|Check PSR-4 compliance|✓||
| `ReturnTypeDeclaration`|Enforce return type declaration|||
| `StaticClosure`|Points closures that can be `static`.||✓|
| `VariablesName`|Check variable (and properties) names|✓||

For **notes and configuration** see [`/inpsyde-custom-sniffs.md`](/inpsyde-custom-sniffs.md) file in this repo.

-------------

## Template Rules

The `InpsydeTemplates` ruleset extends the standard `Inpsyde` ruleset with some template-specific
sniffs while disabling some rules that are not useful in templating context.

The recommended way to use the `InpsydeTemplates` ruleset is as follows:

```xml
<ruleset>
    <file>./src/</file>
    <file>./tests</file>
    <file>./templates</file>
    <file>./views</file>

    <rule ref="Inpsyde">
        <exclude-pattern>*/templates/*</exclude-pattern>
        <exclude-pattern>*/views/*</exclude-pattern>
    </rule>

    <rule ref="InpsydeTemplates">
        <include-pattern>*/templates/*</include-pattern>
        <include-pattern>*/views/*</include-pattern>
    </rule>
</ruleset>
```
The following `Inpsyde` rules are disabled:
* `NoElse`

The following templates-specific rules are available:

| Sniff name          | Description                                       | Has Config | Auto-Fixable |
|:--------------------|:--------------------------------------------------|:----------:|:------------:|
| `TrailingSemicolon` | Remove trailing semicolon before closing PHP tag. |            |      ✓       |

# Removing or Disabling Rules

## Rules Tree

Sometimes it is necessary to don't follow some rules.
To avoid error reporting is is possible to:

- Removing rules for an entire project via configuration
- Disabling rules from code, only is specific places

In both cases it is possible to remove or disable:

- a whole standard
- a standard subset
- a single sniff
- a single rules

The for things above are in hierarchical relationship: a _standard_ is made of one
or more _subset_, each subset contains one or more _sniff_ and each sniff contains
one or more rule.

## Remove rules via configuration file

Rules can be removed for the entire project by using a custom `phpcs.xml`, with
 a syntax like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

	<rule ref="Inpsyde">
		<exclude name="PSR1.Classes.ClassDeclaration"/>
	</rule>

</ruleset>
```

In the example above, the _sniff_ `PSR1.Classes.ClassDeclaration` (and all the rules
it contains) has been removed.

Replacing `PSR1.Classes.ClassDeclaration` with just `PSR1` had been possible to
remove the whole standard, while replacing it with `PSR1.Classes.ClassDeclaration.MultipleClasses`
only the single rule is removed.

## Remove rules via code comments

If it is necessary to remove a rule/sniff/standard subset/standard only in 
specific place in the code, it is possible to use special comments that starts
with:

```php
// phpcs:disable
```

followed by the what you want to to remove.

For example: `// phpcs:disable PSR1.Classes.ClassDeclaration`.

From the point the comment is encountered to the end of the file, the requested
rule/sniff/standard subset/standard is not checked anymore.

To re-enable it is necessary to use a similar syntax, but this time using 
`phpcs:enable` instead of `phpcs:disable`.

It worth noting:

- `phpcs:disable` and `phpcs:enable` can be used without specifying the rule name, in this
  case the check for *all* rules are disabled/enabled.
- Disabling / enabling comments could be embedded in doc block comments at
  file/class/method level. For example:
  
```php
class Foo
{
    /**
     * @param mixed $a
     * @param mixed $b
     *
     * phpcs:disable NeutronStandard.Functions.TypeHint.NoArgumentType
     */
    public function test($a, $b)
    {
        // phpcs:enable
    }
}
```


-------------


# IDE integration

## PhpStorm

After having installed the package as explained above in the _"Installation"_ section,
open PhpStorm settings, and navigate to

`Language & Frameworks` ->  `PHP` -> `Quality Tools` -> `PHP_CodeSniffer`

Choose _"Local"_ in the _"Configuration"_ dropdown.

Click the _"..."_ button next to the dropdown, it will show a dialog
where you need to specify the path for the Code Sniffer executable.

Open the file selection dialog, navigate to `vendor/bin/` in your project and select `phpcs`
(`phpcs.bat` on Windows). 

Click the _"Validate"_ button next to the path input field, if everything is fine
a success message will be shown at the bottom of the window.

Navigate PhpStorm settings to:

`Editor` ->  `Inspections`

Type `codesniffer` in the search field before the list of inspections, 
select `PHP` -> `Quality Tools` -> `PHP_CodeSniffer validation` and enable it using the checkbox in 
the list, press _"Apply"_.

Select  _"PHP_CodeSniffer validation"_, press the refresh icon next to the _"Coding standard"_ 
dropdown on the right and choose `Inpsyde`.

If you do not see `Inpsyde` here, you may need to specify `phpcs.xml` file by selecting _"Custom"_ 
as standard and using the _"..."_ button next to the dropdown.

Now PhpStorm integration is complete, and errors in the code style will be shown in the IDE editor
allowing to detect them without running any commands at all.

# Contribution

## Running tests

To run the tests for this package you need to install its dependencies first:

    $ composer install

After that you can run all PHPUnit tests like this:

    $ vendor/bin/phpunit

The tests are organized in fixture files. If you want to run a single test use the filter argument:

    $ vendor/bin/phpunit --filter function-length-no-blank-lines
