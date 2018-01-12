# Inpsyde PHP Coding Standards

> PHP 7+ coding standards for Inpsyde WordPress projects.

# Installation

The code styles are enforced via the popular [`php_codesniffer`](https://packagist.org/packages/squizlabs/php_codesniffer)
and can be installed via Composer by the name **`inpsyde/php-coding-standards`**.

It means they can be installed by adding the entry ro composer.json `require`:

```json
{
	"require": {
		"inpsyde/php-coding-standards": "^0.3"
	}
}
```

or via command line with: 

```
$ composer require inpsyde/php-coding-standards
```

-------------

# Usage

## Basic usage

When the package is installed via Composer, and dependencies are updated, everything is
ready and the coding standards can be checked via:

```
$ vendor/bin/phpcs --standard="Inpsyde" <path>
```

Where `<path>` is at least one file or directory to check, e.g.:

```
$ vendor/bin/phpcs --standard="Inpsyde" ./src/ ./my-plugin.php
```

On  Windows it would be something like:

```
$ ./vendor/bin/phpcs.bat --standard="Inpsyde" ./src/ ./my-plugin.php
```

There are many options that can be used to customise the behavior of the command, to get documentation use:

```
$ vendor/bin/phpcs --help
```


## Configuration File

To do not have to pass all the arguments to the command line, and to also be able to do
customization it is also possible to create a `phpcs.xml` file that contains something like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

	<description>My Project coding standard.</description>

	<file>./src</file>
	<file>./my-plugin.php</file>

	<config name="text_domain" value="my-project"/>

	<rule ref="Inpsyde"/>

</ruleset>
```

Such a configuration allows to run the code style check with only:

```
$ vendor/bin/phpcs
```

Moreover, thanks to the `text_domain` setting, Code Sniffer will also check that all WP
internationalization functions are called with the proper text domain.

-------------

# Included rules

## PSR-1 & PSR-2

See http://www.php-fig.org/psr/psr-1/ and http://www.php-fig.org/psr/psr-2/


## VariableAnalysis

See https://github.com/sirbrillig/phpcs-variable-analysis


## Neutron Standard

See https://github.com/Automattic/phpcs-neutron-standard

All Neutron Standard rules are included except "Function size" rule.


## Object Calisthenics

Object calisthenics are about software quality.
Some rules are too strict for PHP/WP development, so we keep just some.
See https://github.com/object-calisthenics/phpcs-calisthenics-rules

Included calisthenics rules are:

- Only 1 Level of Indentation per Method
- Do Not Use "else" Keyword
- Do not Abbreviate
- Do not Use Getters and Setters (forbidden public property, no methods that start with "set")
- Keep Your Classes Small (only: max 10 property per class, max 50 lines per method)


## WordPress Coding Standard

To ensure code quality, and compatibility with VIP, several WordPress Coding Standard rules have been
"cherry picked" from WP coding standards.
See https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.

Included rules are:

- `WordPress.VIP.SessionVariableUsage` - Avoid usages of `$_SESSION` super global
- `VIP.SessionFunctionsUsage` - Avoid usages of session-related functions
- `WordPress.VIP.SuperGlobalInputUsage` - Avoid usages of sulerglobals: `$_GET`, `$_POST`, etc
- `WordPress.VIP.ValidatedSanitizedInput` - Avoid non-validated/sanitized input (`$_GET`, `$_POST`, etc)
- `WordPress.Security.EscapeOutput` - Verifies that all outputted strings are escaped.
- `WordPress.Security.NonceVerification` - Checks that nonce verification accompanies form processing.
- `WordPress.WP.AlternativeFunctions.curl` - Discourages the use of cURL functions and suggests WP alternatives.
- `WordPress.WP.DiscouragedConstants` - Warns against usage of discouraged WP constants
- `WordPress.WP.I18n` - Makes sure WP internationalization functions are used properly. Also checks text domain if set in `phpcs.xml`
- `WordPress.Arrays.CommaAfterArrayItem` - Ensure last item of arrays have a comma
- `WordPress.PHP.StrictComparisons` - Enforces Strict Comparison checks
- `WordPress.PHP.StrictInArray` - Prevent calling `in_array()`, `array_search()` and `array_keys()` without `true` as the 3rd parameter
- `WordPress.PHP.POSIXFunctions` - Suggest usage of PCRE functions (`preg_*`) instead of POSIX alternative
- `WordPress.PHP.RestrictedPHPFunctions` - Prevent usage of `create_function`
- `WordPress.PHP.DiscouragedPHPFunctions` - Discourage usage of soem PHP functions (runtime configuration, system calls)
- `WordPress.PHP.DevelopmentFunctions` - Prevent usage of development PHP functions (`error_log`, `var_dump`, `var_export`, `print_r`...)

Any of these rules (just like the others) can be excluded in the `phpcs.xml`, using a syntax like this:

```xml
<?xml version="1.0"?>
<ruleset name="MyProjectCodingStandard">

	<rule ref="Inpsyde">
		<exclude name="WordPress.PHP.DevelopmentFunctions"/>
	</rule>

</ruleset>
```

-------------

# IDE integration

## PhpStorm

After having installed the package as explained above in the _"Installation"_ section,
open PhpStorm settings, and navigate the settings:

`Language & Frameworks` ->  `PHP` -> `Code Sniffer`

There will be a dropdown with label _"Configuration"_, choose _"Local"_.
Next to the dropdown there will be a button with _"..."_ and once clicked will show a dialog
were it is possible to select the path for the Code Sniffer executable.
Navigate inside the `vendor` folder a found the file: `/vendor/bin/phpcs` (`phpcs.bat` in Windows).

Next to the input for path selection there's a button _"Validate"_, click it, if everything is fine
a success message will be shown.

At this point navigate to the settings:

`Editor` ->  `Inspections`

From the list of inspections, expand the _"PHP"_ one and scroll down to _"PHP Code Sniffer validation"_
to enable it.

When selecting _"PHP Code Sniffer validation"_ inspections, on the right there a dropdown to select the
code style.

If you have created a `phpcs.xml` file, select _"Custom"_ as standard, then using the _"..."_ button
next to the dropdown for standard selection, you can pick the `phpcs.xml` file.
In case of no `phpcs.xml` present, it is possible to select _"Inpsyde"_ standard from the dropdown.
If _"Inpsyde"_ is not present in the dropdown, click the "refresh" icon next to the dropdown.

Now PhpStorm integration is complete, and errors in the codestyle will be shown in the IDE editor
so can be recognized without running any command at all.
