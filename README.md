# Inpsyde PHP Coding Standards

> PHP 7+ coding standards for Inpsyde WordPress projects.

# Installation

The code styles are enforced via the popular [`php_codesniffer`](https://packagist.org/packages/squizlabs/php_codesniffer)
and can be installed via Composer by the name **`inpsyde/php-coding-standards`**.

It means they can be installed by adding the entry to composer.json `require-dev`:

```json
{
	"require-dev": {
		"inpsyde/php-coding-standards": "^0.9"
	}
}
```

or via command line with: 

```
$ composer require inpsyde/php-coding-standards --dev
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

See http://www.php-fig.org/psr/psr-1/ and http://www.php-fig.org/psr/psr-2/.

The tree of used rules are listed in the `/docs/rules-list/psr.md` file in this repo.


## Neutron Standard

See https://github.com/Automattic/phpcs-neutron-standard

Almost all Neutron Standard rules are included.

The tree of used rules are listed in the `/docs/rules-list/neutron-standard.md` file in this repo.


## WordPress Coding Standard

To ensure code quality, and compatibility with VIP, several WordPress Coding Standard rules have been
"cherry picked" from WP coding standards.

See https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.

The tree of used rules are listed in the `/docs/rules-list/wordpress.md` file in this repo.


## Generic Rules

Some rules are also included from PHP cCode Sniffer itself. Those rules fall in the
"Generic", "Squiz" and "PEAR" namespace.

Those rules are included by other styles, mainly by PSR-1 and PSR-2.

The tree of used rules are listed in the `/docs/rules-list/generic.md` file in this repo.


## Custom Rules

Few custom rules are also in use.
Customs rules are:

- Enforce argument type declaration, with few exception (e.g. hook callbacks or `ArrayAccess` methods)
- Ensure that any assignment inside conditions in wrapped in parenthesis
- Disallow short open PHP tag
- Use minimum 3 chars for names (with a few exclusions)
- No public class properties
- Max 50 lines per function/method, excluding blank lines and comments-only lines.
- Ensure that actions callbacks do not return anything, while filter callbacks return something.
- Max 100 chars per line, excluding leading indent size and long string inWP translation functions
- Discourage usage of getters and setters.
- Discourage usage of `else`.
- Discourage usage of `define` where `const` is preferable.
- Discourage usage of more than 10 properties per class.
- Enforce return type declaration, with few exceptions (e.g. hook callbacks or `ArrayAccess` methods)
- Check PSR-4 compliance

The tree of used rules are listed in the `/docs/rules-list/custom.md` file in this repo.

### Notes & Configuration

#### Skip `InpsydeCodingStandard.CodeQuality.ReturnTypeDeclaration.NoReturnType` via doc bloc

As of v0.9, when there's no return type declared for a function, but it has a docbloc like:
`@return {$type}|null` and the function _actually_ contains both `null` and not-null return
points **no** warning is shown.
However, if min PHP version is set to 7.1 via php-compatibility `testVersion` config, the warning
**is** shown, because in PHP 7.1 there's the availability for nullable return types.
Also note that the warning **is** shown in case:
 - the `@return` docbloc declares more than one not-null types, e.g. `@return Foo|Bar|null`
 - the `@return` docbloc types contains "mixed", e.g. `@return mixed|null`.
 
 
#### PSR-4 Configuration
`InpsydeCodingStandard.CodeQuality.Psr4` rule needs some configuration to check namespace and
class file paths.
Without configuration the only thing the sniff does is to check that class name and file name match.
The needed configuration mimics the PSR-4 configuration in `composer.json`.
Assuming a `composer.json` like:
```json
{
  "autoload": {
      "psr-4": {
        "Inpsyde\\Foo\\": "src/",
        "Inpsyde\\Foo\\Bar\\Baz\\": "baz/"
      }
    }
}
```
the rule configuration should be:
```xml
<rule ref="InpsydeCodingStandard.CodeQuality.Psr4">
    <properties>
        <property name="psr4" type="array" value="Inpsyde\Foo=>src,Inpsyde\Foo\Bar\Baz=>baz" />
    </properties>
</rule>
```
Please note that when a PSR-4 configuration is given, *all* autoloadable entities (classes/interfaces/trait)
are checked to be compliant.
If there are entities in the sniffer target paths that are not PSR-4 compliant (e.g. loaded via classmap
or not autoloaded at all) those should be excluded via `exclude` property, e.g.
```xml
<rule ref="InpsydeCodingStandard.CodeQuality.Psr4">
    <properties>
        <property name="psr4" type="array" value="Inpsyde\SomePlugin=>src" />
        <property name="exclude" type="array" value="Inpsyde\ExcludeThis,Inpsyde\AndThis" />
    </properties>
</rule>
```

-------------

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

The folder `/docs/rules-list/` of this repo contains 6 files, each of them contain
the rules _tree_ for one (or few related) standard(s).

For example, in the file `docs/rules-list/psr.md` is is possible to read something
like:

```
- PSR1
    - PSR1.Classes
        - PSR1.Classes.ClassDeclaration
            - PSR1.Classes.ClassDeclaration.MultipleClasses
```

Where:
- "_PSR1_" is the standard
- "_PSR1.Classes_" is the subset
- "_PSR1.Classes.ClassDeclaration_" is the sniff
- "_PSR1.Classes.ClassDeclaration.MultipleClasses_" is the rule


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

It worth nothing:

- `phpcs:disable` and `phpcs:enable` can be used without anything else, in this
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
