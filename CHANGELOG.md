# Changelog

## Not released
- Missing return type waring from `ReturnTypeDeclarationSniff` skipped for hook callbacks.
- Fixed a bug in return counting in helper class that affected few sniffs.
- Add several "Generic" and "Squiz" rules.
- Add `DisallowShortOpenTagSniff` that extends the generic sniff allowing short echo.

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
