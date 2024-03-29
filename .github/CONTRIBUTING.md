Thanks for contributing&mdash;you rock!

# Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free).
* See if your issue has been discussed (or even fixed) earlier. You can [search for existing issues](../../../issues?q=is%3Aissue).
* Assuming it does not already exist, [create a new issue](../../../issues/new).
    * Clearly describe the issue. In case you want to report a bug, include steps to reproduce it.
    * Make sure you fill in the earliest version that you know has the issue.
* Fork the repository on GitHub.

# Making Changes

* Create a topic branch from where you want to base your work.
    * Only target release branches if you are certain your fix must be on that branch.
    * To quickly create a topic branch based on the `master` branch:
        * `git checkout -b issue/%YOUR-ISSUE-NUMBER%_%DESCRIPTIVE-TITLE% version/2`
        * a good example is `issue/123_typo_in_readme`
* Make commits of logical units.
* Make sure your commit messages are helpful.

## Quality checks

Before submitting a PR, please run code quality checks via:

```shell
composer qa
```

The command will run PHPCS and Psalm checks as well as PHPUnit tests.

### Run specific unit tests

Unit tests are organized in fixture files (see [`/tests/unit/fixtures`](https://github.com/inpsyde/php-coding-standards/tree/version/2/tests/unit/fixtures)).

To run a single test use the `--filter` option:

```shell
composer tests -- --filter function-length-no-blank-lines
```

# Submitting Changes

* Push your changes to the according topic branch in your fork of the repository.
* [Create a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request)
  to our repository pointing the default branch.
* Wait for feedback. The team looks at pull requests on a regular basis.

# License

By contributing code, you grant its use under the [MIT License](../LICENSE).