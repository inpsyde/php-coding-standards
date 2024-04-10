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

Here, `<path>` is at least one file or directory to check, for example:

```shell
vendor/bin/phpcs --standard="Inpsyde" ./src/ ./my-plugin.php
```

There are many options that can be used to customise the behavior of the command, to get
documentation use:

```shell
vendor/bin/phpcs --help
```

## Configuration File

A `phpcs.xml.dist` file can be used to avoid passing many arguments via the command line.
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
        <exclude name="WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize"/>
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

Such a configuration allows to run the code style check like so:

```shell
vendor/bin/phpcs
```

Moreover, thanks to the `text_domain` setting, PHP_CodeSniffer will also check that all WordPress
internationalization functions are called with the proper text domain.

---

# Included rules

For the detailed lists of included rules, refer to [`ruleset.xml`](./Inpsyde/ruleset.xml).

## PSR-1, PSR-2, PSR-12

For more information about included rules from PHP Standards Recommendations (PSR), refer to the
official documentation:

- [PSR-1](https://www.php-fig.org/psr/psr-1)
- [PSR-2](https://www.php-fig.org/psr/psr-2)
- [PSR-12](https://www.php-fig.org/psr/psr-12)

## WordPress Coding Standards

To ensure code quality, and compatibility with WordPress VIP, some rules have been included from:

- [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards)
- [VIP Coding Standards](https://github.com/Automattic/VIP-Coding-Standards)

## Slevomat

A few rules have been included from the [Slevomat Coding Standard](https://github.com/slevomat/coding-standard).

## PHPCompatibility

For PHP cross-version compatibility checks, the full [PHP Compatibility Coding Standard for PHP CodeSniffer](https://github.com/PHPCompatibility/PHPCompatibility)
standard has been included.

The target PHP version (range) can be changed via a [custom `phpcs.xml` file](https://github.com/PHPCompatibility/PHPCompatibility/blob/9.3.5/README.md#using-a-custom-ruleset).

## Generic Rules

Some rules are also included from PHP_CodeSniffer itself, as well as [PHPCSExtra](https://github.com/PHPCSStandards/PHPCSExtra).

## Custom Rules

The following custom rules are in use:

| Sniff Name                 | Description                                                                                    | Has Config | Auto-Fixable |
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

For **notes and configuration**, refer to the [`inpsyde-custom-sniffs.md`](/inpsyde-custom-sniffs.md)
file in this repository.

---

## Template Rules

The `InpsydeTemplates` ruleset extends the standard `Inpsyde` ruleset with some template-specific
sniffs.

The recommended way to use the `InpsydeTemplates` ruleset is as follows:

```xml
<ruleset>
    <file>./src</file>
    <file>./templates</file>
    <file>./tests</file>
    <file>./views</file>

    <rule ref="Inpsyde"/>

    <rule ref="InpsydeTemplates">
        <include-pattern>*/templates/*</include-pattern>
        <include-pattern>*/views/*</include-pattern>
    </rule>
</ruleset>
```

The following template-specific rules are available:

| Sniff Name          | Description                                       | Has Config | Auto-Fixable |
|:--------------------|:--------------------------------------------------|:----------:|:------------:|
| `TrailingSemicolon` | Remove trailing semicolon before closing PHP tag. |            |      ✓       |

# Removing or Disabling Rules

## Rules Tree

Sometimes it is necessary not to follow some rules. To avoid error reporting, it is possible to:

- remove rules for an entire project via configuration;
- disable rules from code, only is specific places.

In both cases, it is possible to remove or disable:

- a complete standard;
- a standard subset;
- a single sniff;
- a single rule.

These things are in a hierarchical relationship: _standards_ are made of one or more _subsets_, 
which contain one or more _sniffs_, which in turn contain one or more _rules_.

## Removing Rules via Configuration File

Rules can be removed for the entire project by using a custom `phpcs.xml` file, like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

    <rule ref="Inpsyde">
        <exclude name="PSR1.Classes.ClassDeclaration"/>
    </rule>

</ruleset>
```

In the example above, the `PSR1.Classes.ClassDeclaration` sniff (and all the rules it contains)
has been removed.

By using `PSR1` instead of `PSR1.Classes.ClassDeclaration`, one would remove the entire `PSR1`
standard, whereas using `PSR1.Classes.ClassDeclaration.MultipleClasses` would remove this one rule
only, but no other rules in the `PSR1.Classes.ClassDeclaration` sniff.

## Removing Rules via Code Comments

Removing a rule/sniff/subset/standard only for a specific file or a part of it can be done by using
special `phpcs` annotations/comments, for example, `// phpcs:disable` followed by an optional name
of a standard/subset/sniff/rule. Like so:

```php
// phpcs:disable PSR1.Classes.ClassDeclaration
```

For more information about ignoring files, please refer to the official [PHP_CodeSniffer Wiki](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Advanced-Usage#ignoring-parts-of-a-file).

---

# IDE Integration

## PhpStorm

After installing the package as explained above, open PhpStorm settings, and navigate to

`Language & Frameworks` ->  `PHP` -> `Quality Tools` -> `PHP_CodeSniffer`

Choose _"Local"_ in the _"Configuration"_ dropdown.

Click the _"..."_ button next to the dropdown. It will show a dialog where you need to specify
the path for the PHP_CodeSniffer executable.

Open the file selection dialog, navigate to `vendor/bin/` in your project, and select `phpcs`.
On Windows, choose `phpcs.bat`.

Click the _"Validate"_ button next to the path input field. If everything is working fine, a
success message will be shown at the bottom of the window.

Still in the PhpStorm settings, navigate to:

`Editor` ->  `Inspections`

Type `codesniffer` in the search field before the list of inspections, then select:

`PHP` -> `Quality Tools` -> `PHP_CodeSniffer validation`

Enable it using the checkbox in the list, press _"Apply"_.

Select _"PHP_CodeSniffer validation"_, click the refresh icon next to the _"Coding standard"_ 
dropdown on the right, and choose `Inpsyde`.

If you don't see `Inpsyde` here, you may need to specify the `phpcs.xml` file by selecting
_"Custom"_ as standard and then use the _"..."_ button next to the dropdown.

Once the PhpStorm integration is complete, warnings and errors in your code will automatically be
shown in your IDE editor.

---

# Installation

Via Composer, require as development dependency:

```shell
composer require "inpsyde/php-coding-standards:^2@dev" --dev
```

_(Please note that `@dev` can be removed as soon as a stable 2.0.0 version has been released, or if
your root package minimum stability is `dev`)._
