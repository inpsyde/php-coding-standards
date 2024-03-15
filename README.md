# Syde PHP Coding Standards

> PHP 7.4+ coding standards for Syde WordPress projects.

![PHP Quality Assurance](https://github.com/inpsyde/php-coding-standards/workflows/PHP%20Quality%20Assurance/badge.svg)

---

# Usage

When the package is installed via Composer, and dependencies are updated, everything is ready and 
the coding standards can be checked via:

```shell
vendor/bin/phpcs --standard="Inpsyde" <path>
```

Where `<path>` is at least one file or directory to check, e.g.:

```shell
vendor/bin/phpcs --standard="Inpsyde" ./src/ ./my-plugin.php
```

There are many options that can be used to customise the behavior of the command, to get
documentation use:

```shell
vendor/bin/phpcs --help
```

## Configuration File

A `phpcs.xml.dist` can be used to avoid passing many arguments via command line.
For example:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

    <description>My Project coding standard.</description>

    <file>./src</file>
    <file>./tests/src</file>

    <arg value="sp"/>
    <arg name="colors"/>

    <config name="testVersion" value="7.4-"/>
    <config name="text_domain" value="my-project"/>
    
    <rule ref="Inpsyde">
        <exclude name="WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize" />
    </rule>
    
    <rule ref="Inpsyde.CodeQuality.Psr4">
        <properties>
            <property
                name="psr4"
                type="array"
                value="
                    Inpsyde\MyProject=>src,
                    Inpsyde\MyProject\Tests=>tests/src|tests/unit
                "/>
        </properties>
    </rule>

</ruleset>
```

Such a configuration allows to run the code style check with only:

```shell
vendor/bin/phpcs
```

Moreover, thanks to the `text_domain` setting, Code Sniffer will also check that all WP
internationalization functions are called with the proper text domain.

---

# Included rules

For the detailed lists of included rules refers to [`ruleset.xml](./Inpsyde/ruleset.xml).

## PSR-1, PSR-2, PSR-12

See https://www.php-fig.org/psr/psr-1, https://www.php-fig.org/psr/psr-2,
https://www.php-fig.org/psr/psr-12

## WordPress Coding Standard

To ensure code quality, and compatibility with wp.com VIP, several WordPress Coding Standard rules 
have been"cherry-picked" from
[WP coding standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) and
[VIP coding standard](https://github.com/Automattic/VIP-Coding-Standards/)

## Slevomat

A few rules are cherry-picked from ["Slevomat" coding standard](https://github.com/slevomat/coding-standard).

## PHPCompatibility

See https://github.com/wimg/PHPCompatibility.

It allows to analyse code for compatibility with higher and lower versions of PHP.
The default target version is PHP 7.0+.

Target version can be changed via custom `phpcs.xml`.

## Generic Rules

Some rules are also included from PHPCS itself and [PHPCS Extra](https://github.com/PHPCSStandards/PHPCSExtra).

## Custom Rules

Some custom rules are also in use. They are:

| Sniff name                 | Description                                                                                    | Has Config | Auto-Fixable |
|:---------------------------|:-----------------------------------------------------------------------------------------------|:----------:|:------------:|
| `ArgumentTypeDeclaration`  | Enforce argument type declaration.                                                             |            |              |
| `DisableCallUserFunc`      | Disable usage of `call_user_func`.                                                             |            |              |
| `DisableMagicSerialize`    | Disable usage of `__serialize`, `__sleep`, `__unserialize`, `__wakeup`.                        |            |              |
| `DisallowShortOpenTag`     | Disallow short open PHP tag (short echo tag allowed).                                          |            |              |
| `ElementNameMinimalLength` | Use minimum 3 chars for names (with a few exclusions)                                          |     ✓      |              |
| `EncodingComment`          | Detect usage of opening `-*- coding: utf-8 -*-`                                                |     ✓      |      ✓       |
| `ForbiddenPublicProperty`  | No public class properties                                                                     |            |              |
| `FunctionBodyStart`        | Handle blank line at start of function body.                                                   |            |      ✓       |
| `FunctionLength`           | Max 50 lines per function/method, excluding blank lines and comments-only lines.               |     ✓      |              |
| `HookClosureReturn`        | Ensure that actions callbacks do not return anything, while filter callbacks return something. |            |              |
| `HookPriority`             | Report usage of `PHP_INT_MAX` and `PHP_INT_MIN` as hook priority.                              |            |              |
| `LineLength`               | Max 100 chars per line                                                                         |     ✓      |              |
| `NestingLevel`             | Max indent level of 3 inside functions                                                         |     ✓      |              |
| `NoAccessors`              | Discourage usage of getters and setters.                                                       |            |              |
| `NoElse`                   | Discourage usage of `else`.                                                                    |            |              |
| `NoRootNamespaceFunctions` | Report usage of global functions in the root namespace.                                        |            |              |
| `NoTopLevelDefine`         | Discourage usage of `define` where `const` is preferable.                                      |            |              |
| `PropertyPerClassLimit`    | Discourage usage of more than 10 properties per class.                                         |     ✓      |              |
| `Psr4`                     | Check PSR-4 compliance                                                                         |     ✓      |              |
| `ReturnTypeDeclaration`    | Enforce return type declaration                                                                |            |              |
| `StaticClosure`            | Points closures that can be `static`.                                                          |            |      ✓       |
| `VariablesName`            | Check variable (and properties) names                                                          |     ✓      |              |

For **notes and configuration** see [`/inpsyde-custom-sniffs.md`](/inpsyde-custom-sniffs.md) file in this repo.

---

## Template Rules

The `InpsydeTemplates` ruleset extends the standard `Inpsyde` ruleset with some template-specific
sniffs.

The recommended way to use the `InpsydeTemplates` ruleset is as follows:

```xml
<ruleset>
    <file>./src/</file>
    <file>./tests</file>
    <file>./templates</file>
    <file>./views</file>

    <rule ref="Inpsyde" />

    <rule ref="InpsydeTemplates">
        <include-pattern>*/templates/*</include-pattern>
        <include-pattern>*/views/*</include-pattern>
    </rule>
</ruleset>
```

The following templates-specific rules are available:

| Sniff name          | Description                                       | Has Config | Auto-Fixable |
|:--------------------|:--------------------------------------------------|:----------:|:------------:|
| `TrailingSemicolon` | Remove trailing semicolon before closing PHP tag. |            |      ✓       |

# Removing or Disabling Rules

## Rules Tree

Sometimes it is necessary to don't follow some rules. To avoid error reporting it is possible to:

- Removing rules for an entire project via configuration
- Disabling rules from code, only is specific places

In both cases it is possible to remove or disable:

- a whole standard
- a standard subset
- a single sniff
- a single rules

The for things above are in hierarchical relationship: a _standard_ is made of one or more _subset_, 
each subset contains one or more _sniff_ and each sniff contains one or more rule.

## Remove rules via configuration file

Rules can be removed for the entire project by using a custom `phpcs.xml`, with a syntax like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

	<rule ref="Inpsyde">
		<exclude name="PSR1.Classes.ClassDeclaration"/>
	</rule>

</ruleset>
```

In the example above, the _sniff_ `PSR1.Classes.ClassDeclaration` (and all the rules it contains)
has been removed.

Replacing `PSR1.Classes.ClassDeclaration` with just `PSR1` had been possible to
remove the whole standard, while replacing it with `PSR1.Classes.ClassDeclaration.MultipleClasses`
only the single rule is removed.

## Remove rules via code comments

If it is necessary to remove a rule/sniff/standard subset/standard only in specific place in the 
code, it is possible to use special comments that starts with `// phpcs:disable` followed by the 
name of the sniff to disable.

For example: `// phpcs:disable PSR1.Classes.ClassDeclaration`.

From the point the comment is encountered to the end of the file, the requested rule/sniff/standard 
subset/standard is not checked anymore.

To re-enable it is necessary to use a similar syntax, but this time using `phpcs:enable` instead of 
`phpcs:disable`.

It is worth noting:

- `phpcs:disable` and `phpcs:enable` can be used without specifying the rule name, in this case the
  check for *all* rules are disabled/enabled.
- Disabling / enabling comments could be embedded in doc block comments at file/class/method level.
  For example:
  
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

---

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

---

# Installation

Via Composer, require as dev-dependency:

```shell
composer require "inpsyde/php-coding-standards:^2@dev" --dev
```

_(the `@dev` can be removed as soon as the stable 2.0.0 will be released, or if root package minimum stability is "dev")._
