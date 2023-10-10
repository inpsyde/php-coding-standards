# Inpsyde Custom Sniffs

## Sniffs list

- Inpsyde.CodeQuality.ArgumentTypeDeclaration
- Inpsyde.CodeQuality.ConstantVisibility
- Inpsyde.CodeQuality.DisallowShortOpenTag
- Inpsyde.CodeQuality.ElementNameMinimalLength
- Inpsyde.CodeQuality.ForbiddenPublicProperty
- Inpsyde.CodeQuality.FunctionBodyStart
- Inpsyde.CodeQuality.FunctionLength
- Inpsyde.CodeQuality.HookClosureReturn
- Inpsyde.CodeQuality.LineLength
- Inpsyde.CodeQuality.NestingLevel
- Inpsyde.CodeQuality.NoAccessors
- Inpsyde.CodeQuality.NoElse
- Inpsyde.CodeQuality.NoTopLevelDefine
- Inpsyde.CodeQuality.PropertyPerClassLimit
- Inpsyde.CodeQuality.Psr4
- Inpsyde.CodeQuality.ReturnTypeDeclaration
- Inpsyde.CodeQuality.StaticClosure
- Inpsyde.CodeQuality.VariablesName

Below there's a description, notes, and possible configuration for all the sniffs.

## Inpsyde.CodeQuality.ArgumentTypeDeclaration

Ensures that all functions, methods and closures use type declaration for their arguments.
There are a few exceptions:

- Methods of `ArrayAccess`, because PHP don't allow type declarations for them
- `unserialize` and `seek` methods, because PHP don't allow type declarations for them
- Functions, methods and closures attached to WordPress hooks, because it is discouraged to declare
  types in WordPress hooks. Closures used for hooks are auto-detected, for functions and methods the
  sniff relies on `@wp-hook` annotation
  
This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.ConstantVisibility

Make sure that all class constants use visibility modifier.
This sniff is inherited from default PSR-12 but, unlike the original sniff, it does nothing if the
declared supported PHP version is lower than 7.1.
The supported PHP version is taken from `testVersion` PHPCS config.

This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.DisallowShortOpenTag

Prevents the usage of short open tag.
Inherited from `Generic.DisallowShortOpenTag` sniff, unlike the original, this custom rule allows
the usage of echo short open tag (`<?=`).

This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.ElementNameMinimalLength

Make sure all functions, classes, interfaces, trait and constants use a minimal length (by default,
3 characters) for their name.
There's a whitelist of names that are allowed even if one or two characters long.

It is possible to configure the desired minimal length via `minLength` config, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.ElementNameMinimalLength">
    <properties>
        <property name="minLength" value="5" />
    </properties>
</rule>
```

It is also possible to override the whitelist of allowed names via `allowedShortNames` config, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.ElementNameMinimalLength">
    <properties>
        <property name="allowedShortNames" type="array" value="id,db,ok,x,y" />
    </properties>
</rule>
```

alternatively, whitelist can be extended via `additionalAllowedNames` config, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.ElementNameMinimalLength">
    <properties>
        <property name="additionalAllowedNames" type="array" value="x,y" />
    </properties>
</rule>
```

-----

## Inpsyde.CodeQuality.ForbiddenPublicProperty

Prevent usage of public properties in classes.

This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.FunctionBodyStart

Enforces a blank (or comment) line before of function body.

It is tolerated to don't have a blank line before body for functions whose signature is single-line
with opening curly bracket is on next-line, like the following: 

```php
function foo(string $foo, string $bar): bool
{
    echo $foo . $bar;
    
    return true;
}
```

This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.FunctionLength

Trigger a warning if the functions is longer than a configured length (by default 5 lines).
By default, in the counting are not considered:
- white lines
- comments
- doc bloc _inside_ function block

The maximum allowed is configurable via `maxLength` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.FunctionLength">
    <properties>
        <property name="maxLength" value="20" />
    </properties>
</rule>
```

It is also possible to include in the counting what's normally excluded via the properties:

- `ignoreBlankLines`
- `ignoreComments`
- `ignoreDocBlocks`

for example:

```xml
<rule ref="Inpsyde.CodeQuality.FunctionLength">
    <properties>
        <property name="ignoreBlankLines" value="false" />
        <property name="ignoreDocBlocks" value="false" />
        <property name="ignoreComments" value="false" />
    </properties>
</rule>
```

-----

## Inpsyde.CodeQuality.HookClosureReturn

Analyses closures attached to WordPress hooks ensuring that:

- functions attached to _filters_ return something
- functions attached to _actions_ don't return anything

This sniff has no available configuration.

-----
## Inpsyde.CodeQuality.HookPriorityLimit

Raises a warning if:

- `PHP_INT_MAX` is used for `add_filter`.
- `PHP_INT_MIN` is used for `add_action` or `add_filter`.
-----

## Inpsyde.CodeQuality.LineLength

Ensures that any line of code or comment is less than a maximum number of characters, by default 100.
There are three exceptions:
- Lines that contain WordPress long strings used in WP translation functions.
  Because splitting the text to translate would be against WPCS.
- Lines that contain long single words, for example URLs.
  Because it does not make sense to split a single word in multiple lines.
- Lines in inline HTML with a single attribute that is over the line length. 
  While a tag with multiple attributes can be easily written with an attribute per line, when a single
  attribute is already over the limit split it across multiple lines does not really make sense.

The maximum length is configurable via the `lineLimit` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.LineLength">
    <properties>
        <property name="lineLimit" value="120" />
    </properties>
</rule>
```

-----

## Inpsyde.CodeQuality.NestingLevel

Ensures that inside any function or method the max nesting level is inside a given limit.

For example, for the code:

```php
function test(bool $level_one, array $level_two, bool $level_three)
{
    if ($level_one) {
        foreach ($level_two as $value) {
            if ($level_three) {
                return $value;
            }
        }
    }

    return '';
}
```

The nesting level is 3.

By default, the sniff triggers a _warning_ if nesting is equal or bigger than 3, and  triggers
an _error_ if nesting is equal or bigger than 5.

The warning and error limit can be customized via, respectively, `warningLimit` and `errorLimit`
properties:

```xml
<rule ref="Inpsyde.CodeQuality.NestingLevel">
    <properties>
        <property name="warningLimit" value="5" />
        <property name="errorLimit" value="10" />
    </properties>
</rule>
```

There's an exception. Normally a `try`/`catch`/`finally` blocks accounts for a nesting level,
but this sniff ignores the increase of level causes by a `try`/`catch`/`finally` that is found
immediately inside the level of function.

For example, the following code would be fine:

```php
function test(array $data, string $append): string
{
    // Indent level 1
    try {
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        // Indent level 2
        if ($encoded) {
            // Indent level 3
            if ($append !== '') {
                return $encoded . $append;
            }

            return $encoded;
        }

        return '';
    } catch (\Throwable $e) {
        return '';
    }
}
```

In fact, the two nested `if`s would account for an indent level of 2, plus the `try`/`catch`
block that would be 3, but because the `try`/`catch` is directly inside the function it is ignored,
so the max level considered by the sniff is 2, which is inside the limit.

This exception in the regard of `try`/`catch`/`finally` blocks can be disabled via the
`ignoreTopLevelTryBlock` property: 

```xml
<rule ref="Inpsyde.CodeQuality.NestingLevel">
    <properties>
        <property name="errorLimit" value="10" />
        <property name="ignoreTopLevelTryBlock" value="false" />
    </properties>
</rule>
```

-----

## Inpsyde.CodeQuality.NoAccessors

Trigger a warning when accessors are used.

By default, only public and protected methods are taken into consideration, but it is possible to 
include private method via the `skipForPrivate` property.
The `skipForProtected` property can be used to ignore protected methods.

```xml
<rule ref="Inpsyde.CodeQuality.NoAccessors">
    <properties>
        <property name="skipForProtected" value="true" />
    </properties>
</rule>
```

### Note 1
An accessor is a methods that either _set_ or _get_ a class property.
With code sniffer it is not possible to determine what a method actually _does_, so this sniff just
rely on the method name: any method whose name starts with `get` or `set` will be considered an
accessor by the sniff. 
If a method starts with  _set_ or _get_ but is not an accessor, please ignore or disable this rule
for it.

### Note 2
Setters are discouraged because alternative constructs and patterns, like constructor injection and
immutability are preferable.
In the great, great majority of cases don't use setters improve code design.
That said, if you're sure that your case is inside the minority of cases where no alternative is
possible or desirable, feel free to disable the rule for your case.

### Note 3
Getters are discouraged because _often_ are a symptom of bad design, were object properties are
"leaked" breaking encapsulation and _often_ by applying principles like ["Tell Don't Ask"](https://martinfowler.com/bliki/TellDontAsk.html)
it is possible to improve code design without using getters.
This rule is also part of [Object Calisthenics](https://williamdurand.fr/2013/06/03/object-calisthenics/#9-no-getterssettersproperties)
invented by Jeff Bay in his book [The ThoughtWorks Anthology](https://pragprog.com/book/twa/thoughtworks-anthology).
However, must be noted that:
- Code sniffer is not capable of analysing what a method actual _does_. It is very possible that
  what this sniff considers a getter is actually fine, and/or alternative are worse.
  Feel free to disable the rule in such cases.
- It is not rare that having methods that return object properties are _required_.
  For example, an entity very likely needs a method that returns its ID.
  That method would be an accessor, and would be "historically" named `getId()`.
  There's nothing _bad_ about it,   so in such cases the developer should be very free to ignore or disable the rule.
  That said, different authors who, inspired by the object oriented programming as intended by its creator,
  [Alan Kay](https://it.wikipedia.org/wiki/Alan_Kay), advocate for a design that does not consider
  methods _"what an object do"_ but consider methods as _"messages"_ that objects exchange.
  "Messages" might be different in nature, and just like messages that humans exchange can be
  interrogative, imperative, or informative in their nature.
  Following this principle, a method that returns an entity ID can be considered a message that an object
  sends to other objects  to inform them about its ID and, as such, `getId()` makes no sense as method name,
  whereas a name like `heresMyId()` would be more fitting, and shortening it to `id()` would make sense.
  This is why in warning messages the sniff suggests to use `id()` instead of `getId()`.
  However, following this line of thinking is not required nor mandatory.
  Using `id()` instead of `getId()` might be considered for a developer a quick and "well enough" way
  to avoid the warning from the sniff, without embracing the philosophy behind.
  But any developer at Inpsyde that in such cases prefers to continue using `getId()` and 
  disabling/ignoring the sniff is, once again, welcome to do so.

-----

## Inpsyde.CodeQuality.NoElse

Implements the rule against usage of `else` in favor of early returns.

This rule is also part of [Object Calisthenics](https://williamdurand.fr/2013/06/03/object-calisthenics/#9-no-getterssettersproperties)
invented by Jeff Bay in his book [The ThoughtWorks Anthology](https://pragprog.com/book/twa/thoughtworks-anthology).

This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.NoTopLevelDefine

In PHP there're two ways to define global/namespaced constants (that is constants that are not
class constants): `define` and `const`.

`define` is a function that is executed at runtime, whereas `const` is a language construct that 
is parsed a "compile time", that is when PHP code is converted in bytecode _before_ it is executed. 

Besides the usual differences between functions and language constructs, being parsed at compile time
allows constant defined by `const` to be cached via [OPcache](https://www.php.net/manual/en/book.opcache.php) 
or via [PHP 7.4+ preloading](https://wiki.php.net/rfc/preload).

Being parsed at compile time also means that `const` can be used is constructs that depends on runtime,
for example conditionals.

All that means that `const` is preferable, and should be used when possible, using `define` only
inside conditionals or in any other case `const` can be used.

This sniff has no available configuration.

-----

## Inpsyde.CodeQuality.PropertyPerClassLimit

Ensures a maximum number of property per class, by default 10.
That number is configurable via `maxCount` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.PropertyPerClassLimit">
    <properties>
        <property name="maxCount" value="120" />
    </properties>
</rule>
```

-----

## Inpsyde.CodeQuality.Psr4

Our style enforce the use of PSR-4 for autoload.
This sniff make use of some configuration to check that files that contain classes are saved using 
the structure expected by PSR-4.
If no configuration is provided the sniff only checks that class name and match file name, which is
not a warranty of PSR-4.

The needed configuration is specular to the PSR-4 configuration in `composer.json`.
Assuming a `composer.json` like:

```json
{
  "autoload": {
    "psr-4": {
      "Inpsyde\\Foo\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Inpsyde\\Foo\\Tests\\": "tests/php/"
    }
  }
}
```
the rule configuration should be:

```xml
<rule ref="Inpsyde.CodeQuality.Psr4">
    <properties>
        <property name="psr4" type="array" value="Inpsyde\Foo=>src,Inpsyde\Foo\Tests=>tests/php" />
    </properties>
</rule>
```

Please note that when a PSR-4 configuration is given, *all* autoloadable entities (classes/interfaces/trait)
are checked to be compliant.
If there are entities in the sniffer target paths that are not PSR-4 compliant (e.g. loaded via classmap
or not autoloaded at all) those should be excluded via `exclude` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.Psr4">
    <properties>
        <property name="psr4" type="array" value="Inpsyde\SomePlugin=>src" />
        <property name="exclude" type="array" value="Inpsyde\ExcludeThis,Inpsyde\AndThis" />
    </properties>
</rule>
```

Note that anything that *starts with* any of the values in the `exclude` array will be excluded.

E.g. by excluding `Inpsyde\ExcludeThis` things like `Inpsyde\ExcludeThis\Foo` and
`Inpsyde\ExcludeThis\Bar\Baz` will be excluded as well.

To make sure what's excluded is a namespace, and not a class with same name, just use `\` as last
character.

-----

## Inpsyde.CodeQuality.ReturnTypeDeclaration

Ensures that return type declaration are used.

There are some exceptions:

- `ArrayAccess` methods
- Some PHP methods like: `serialize`, `jsonSerialize`, `getIterator`, `getInnerIterator`, `getChildren`,
  `current`, `key`, valid`, `count`.

When there's support for PHP 7.1+ `void` return type is expected.

When there's support for PHP 7.0, a function without return type declaration that has a doc-block
like:

```php
/** return {$type}|null */
```

will *not* trigger any warning (but it will in case there's support for PHP 7.1+)

### Note

We are fully aware that 100% strictly typed code in PHP is rarely possible, feel free to ignore/disable
the rule when any alternative is worse.

-----

## Inpsyde.CodeQuality.VariablesName

Checks that alla variable names are either `$camelCase` (default) or `$snake_case` variable.

To change the check type, use `checkType` property, and set it to either: `"camelCase"` or `"snake_case"`
(any other value will be ignored and default will be applied).

```xml
<rule ref="Inpsyde.CodeQuality.VariablesName">
    <properties>
        <property name="checkType" value="snake_case" />
    </properties>
</rule>
```

By default, the sniff applies check to both local variables and class properties.

It is possible to ignore either local variables or class properties respectively via `ignoreLocalVars`
and `ignoreProperties` properties.

E.g.:

```xml
<rule ref="Inpsyde.CodeQuality.VariablesName">
    <properties>
        <property name="ignoreLocalVars" value="true" />
    </properties>
</rule>
```

No matter the check type used (`"camelCase"` or `"snake_case"`), PHP super globals variables and
WordPress global variables are always ignored.

It is possible to also ignore some other names via the `ignoredNames` property:

```xml
<rule ref="Inpsyde.CodeQuality.VariablesName">
    <properties>
        <property name="ignoredNames" type="array" value="ALLOWED,allowed_snake" />
    </properties>
</rule>
```

-----

## Inpsyde.CodeQuality.StaticClosure

When a closure does not contain reference to `$this` it could become `static`.
This sniff suggests via a warning when that's the case.
It is auto-fixable.

Must be noted that static closures can't be bound, even if they don't refer `$this`.
In the case a closure that does not contain `$this` needs to be bound, this sniff would wrongly
suggest to make it static.
To tell the sniff that a closure can't be static because needs to be bound it is possible to use the
custom `@bound` annotation, or annotate a `@var SomeClass $this`.

For example, in the following code no warnings would be raised by the sniff:

```php
/** @bound */
$a = function () {
    return 'Foo';
};

/** @var Foo $this */
$b = function () {
    return 'Foo';
};

$foo = new Foo();

$a->call($foo);
\Closure::bind($b, $foo)();
```
