# Changelog

## Not released
* Fix a bug in `FunctionLengthSniff` which only excludes first doc block
* `FunctionLengthSniff` also excludes (by default) blank lines and single line comments
* Introduce `phpcs.xml`

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
