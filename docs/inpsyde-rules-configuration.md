# Inpsyde Rules Configuration

## Inpsyde.CodeQuality.ElementNameMinimalLength

It is possible to configure the minimal length required to don't trigger any warning (by default 3).
That is possible via `minLength` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.ElementNameMinimalLengthSniff">
    <properties>
        <property name="minLength" value="5" />
    </properties>
</rule>
```

It is also possible to configure the whitelist of allowed names with less than 3 (or whatever
the minimal length is configured to) characters.
By default the whitelisted names are:
 - 'i'
 - 'id'
 - 'to'
 - 'up'
 - 'ok'
 - 'no'
 - 'go'
 - 'it'
 - 'db'
 but they can be configured via `allowedShortNames` config, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.ElementNameMinimalLengthSniff">
    <properties>
        <property name="allowedShortNames" type="array" value="id,db,x,y" />
    </properties>
</rule>
```


-----


## Inpsyde.CodeQuality.FunctionLength

It is possible to configure the maximum allowed lines to don't trigger any warning (by default 50).
That is possible via `maxLength` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.FunctionLengthSniff">
    <properties>
        <property name="maxLength" value="20" />
    </properties>
</rule>
```

By default, in the counting are excluded:
- white lines
- comments
- doc bloc _inside_ function block

It is possible to include any/all the lines of those kinds via 3 flags (by default all `true`):

- `ignoreBlankLines`
- `ignoreComments`
- `ignoreDocBlocks`

e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.FunctionLengthSniff">
    <properties>
        <property name="ignoreBlankLines" value="true" />
        <property name="ignoreDocBlocks" value="false" />
        <property name="ignoreComments" value="false" />
    </properties>
</rule>
```

-----


## Inpsyde.CodeQuality.LineLength

It is possible to configure the maximum allowed characters per line to don't trigger any warning
(by default 100).
That is possible via `lineLimit` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.LineLengthSniff">
    <properties>
        <property name="lineLimit" value="120" />
    </properties>
</rule>
```

-----


## Inpsyde.CodeQuality.PropertyPerClassLimit

It is possible to configure the maximum allowed number of property per class to don't trigger any
warning (by default 10).
That is possible via `maxCount` property, e.g.:

```xml
<rule ref="Inpsyde.CodeQuality.PropertyPerClassLimit">
    <properties>
        <property name="maxCount" value="120" />
    </properties>
</rule>
```


## Inpsyde.CodeQuality.Psr4

`Inpsyde.CodeQuality.Psr4` rule needs some configuration to check namespace and
class file paths.
Without configuration the only thing the sniff does is to check that class name and file name match.
The needed configuration mimics the PSR-4 configuration in `composer.json`.
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

When there's no return type declared for a function, but it has a docbloc like:

```php
/**
 * return {$type}|null`
 */
```
and the function _actually_ contains both `null` and not-null return points **no** warning is shown.

However, if a minimum PHP version is set via [PHPCompatibility](https://github.com/wimg/PHPCompatibility)
`testVersion` config, and it is **7.1 or higher**, the warning **is** shown, because in PHP 7.1
there's the availability for nullable return types which should be used in that case.

Also note that the warning **is** shown in case:
 - the `@return` docbloc declares more than one not-null types, e.g. `@return Foo|Bar|null`
 - the `@return` docbloc types contains "mixed", e.g. `@return mixed|null`.
 
-----