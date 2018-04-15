# Changelog

## Not released
- Whitelist PHP core methods for not having return or argument type declaration.

## 0.12.0
- Fix bug in `Psr4Sniff` when class has not namespace.
- Allow @wp-hook ignore of return type for private and protected methods.
- Only check public accessors (by default).
- Fix bug in checking return type.
- Allow filters callbacks to return `null` on purpose.

## 0.11.0
- Fix false positive in `ReturnTypeDeclarationSniff` with nullable types.
- Relax check for missing return type when `{aType}|null` doc bloc is present.
- Add `is` to the list of allowed short names.
- Added `FunctionBodyStartSniff` to deal with blank lines on top of functions body.
- Added `VariablesNameSniff`.
- Improved `PhpcsHelpers::variableIsProperty()`.
- Improved failure handling in FixturesTest.
- Use NeutronStandard by opting-in rules instead of opting-out.
- Properly handle Generators and return types.

## 0.10.0
- Renamed sniffs namespace (**breaking change**).
  Sniff are now referenced via `Inpsyde.CodeQuality...` instead of `InpsydeCodingStandard.CodeQuality...`
- Add `Psr4Sniff` to check PSR-4 compliance of files that contain classes auto-loadable entities.
- Minor tweaks to sniff.
- Improved documentation for custom sniffs and their configuration.

## 0.9.0
- `ReturnTypeDeclarationSniff` do no warn for missing return type when a docbloc like:
  `@return {aType}|null` exists for the function.

## 0.8.0
- Fix bug in `NoAccessorsSniff` and allow for a few method names related to PHP core interfaces.
- Exclude `ArrayAccess` methods from `ReturnTypeDeclarationSniff` and `ArgumentTypeDeclarationSniff`.
- Fix bug in `LineLengthSniff` which affected edge cases.
- Changed default `LineLengthSniff` max length to 100, excluding leading indent.
- Remove Variable Analysis, too much false positives

## 0.7.2
- Fix bug in `ReturnTypeDeclarationSniff` which caused wrong return type detection.

## 0.7.1
- Exclude `NeutronStandard.MagicMethods.RiskyMagicMethod`
- Add `.gitattributes`
- Update own styles in `phpcs`

## 0.7.0
- Removed `NeutronStandard.Conditions.DisallowConditionAssignWithoutConditional`.
- Removed `NeutronStandard.MagicMethods.DisallowMagicGet`.
- Removed `NeutronStandard.MagicMethods.DisallowMagicSet`.
- Made `NeutronStandard.Whitespace.DisallowMultipleNewlines.MultipleNewlines` a warning not error.
- Added custom `AssignmentInsideConditionSniff` sniff.
- Added custom `NoTopLevelDefineSniff` sniff.

## 0.6.0
- Missing return type waring from `ReturnTypeDeclarationSniff` skipped for hook callbacks.
- Fixed a bug in return counting in helper class that affected few sniffs.
- Add several "Generic" and "Squiz" rules.
- Add `DisallowShortOpenTagSniff` that extends the generic sniff allowing short echo.
- Remove `NeutronStandard.Constants.DisallowDefine` because of github.com/Automattic/phpcs-neutron-standard/issues/44
- Renamed configuration properties for `FunctionLengthSniff`.
- Add integration tests for custom sniffs.
- Rename `NoASetterSniff` to `NoAccessorsSniff` and also warn for getters.

## 0.5.1
- `ArgumentTypeDeclarationSniff` also skip function and methods that declare `@wp-hook` doc param.

## 0.5.0
* Disabled `NeutronStandard.Functions.TypeHint` and replaced with custom sniffs
* Added `ArgumentTypeDeclarationSniff` to replace `NeutronStandard.Functions.TypeHint` sniff for
  missing argument types.
  It does not check closures used as hook callbacks (because WP cannot be trusted on types).
* Added `ReturnTypeDeclarationSniff` to replace `NeutronStandard.Functions.TypeHint` sniff for
  missing or wrong return type.
* Added `HookClosureReturnSniff` to sniff closures added to filters and missing return values and
  closures added to action and having return values.

## 0.4.2
* Fix a bug in `FunctionLengthSniff` which only excludes first doc block
* `FunctionLengthSniff` also excludes (by default) blank lines and single line comments
* Introduce `phpcs.xml`
* Small improvements to ruleset
* Use own styles

## 0.4.1
* `FunctionLengthSniff` now excludes doc blocks lines from counting
* New `LineLengthSniff` (that replaces "Generic" sniff included by "PSR2") and
  ignores long lines coming from translation functions first argument

## 0.4.0
* Rename custom rules namespace from `CodingStandard` to `InpsydeCodingStandard`

## 0.3.0
* Usage of PSR 1/2 as base
* Test for PHP 7+
* Introduction of [phpcs-variable-analysis](https://github.com/sirbrillig/phpcs-variable-analysis)
* Introduction of [Automattic NeutronStandard](https://github.com/Automattic/phpcs-neutron-standard)
* Only use few WordPress rules from [wpcs](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)
* Make package compatible with `phpcodesniffer-composer-installer`
* Add lot of info to README

## 0.2.0
* Removed `Generic.PHP.LowerCaseConstant`, because we're going to use PSR standards.
* Updated to newer version of `wp-coding-standards/wpcs`.
* Updated to newer version of `squizlabs/php_codesniffer`.
* Added support for PHP7+.
* Added new excludes which are too WordPressy.

## 0.1.0
* First release.
