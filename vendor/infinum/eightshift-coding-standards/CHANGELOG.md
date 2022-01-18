# Change Log for the Eightshift WordPress Coding Standards

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/) and [Keep a CHANGELOG](https://keepachangelog.com/).

The semantic versioning started from version 0.2.1.

## [Unreleased]

_No documentation available about unreleased changes yet._

## [1.3.0](https://github.com/infinum/coding-standards-wp/compare/1.2.0...1.3.0) - 2020-05-04

### Removed
- Modified escaping sniff
    - The sniff wasn't working correctly, and we'll wait for the upstream to fix the issue
- Exclude doc comment align sniff

### Fixed
- Improve disallow do shortcode sniff (better regex and tests)
- Fixed the namespace in the ruleset.xml

### Changed
- Update minimum supported WP version to 5.4

## [1.2.0](https://github.com/infinum/coding-standards-wp/compare/1.1.0...1.2.0) - 2020-04-15

### Added
- Workflows for GH Actions
- Docs for custom sniffs
- Modified escaping sniff
    - will exclude the custom `Components::render()` method from the eightshift-libs
- Added phpcs extra ruleset for array spacing and array best practices
- Update samples for ruleset

### Changed
- Updated sniffs namespace
  
### Fixed
- Fix docblocks in the sniffs

## [1.1.0](https://github.com/infinum/coding-standards-wp/compare/1.0.1...1.1.0) - 2020-11-30

### Package renaming

We renamed the package from `infinum/coding-standards-wp` to `infinum/eightshift-coding-standards`.

## [1.1.0](https://github.com/infinum/coding-standards-wp/compare/1.0.0...1.0.1) - 2020-09-24

### Added
- Added a rule to prevent underscores denoting the private methods/properties

## [1.0.0](https://github.com/infinum/coding-standards-wp/compare/0.4.1...1.0.0) - 2020-09-18

### Official release of the Eightshift coding standards for WordPress projects

This is the official release of the Eightshift coding standards for WordPress. It contains breaking changes, mostly in
 regard
 of the naming scheme. 
To equate the way we write our PHP and JS we opted to follow a modified PSR standards.
What this means is that we will remove liberal spacing, add some PSR12 modifications regarding arguments placing in closures, change snake_case with CamelCase for classes (for autoload puropses) and some other minor changes that will be documented below.
If you wish to use the old standards, be sure to modify your projects `composer.json` file with the appropriate version.

### Added
- Added PSR-12 standards
- Added more tests
- Updated the WPCS to 2.3.0
- Updated scripts

### Removed
- Removed the Test bootstrap
- Removed PHP 5.6 support and raised the recommended PHP version to 7.2 or higher
- Removed WordPress-Core standards

### Changed
- Replaced WordPress naming standards to modified PSR standards
- Changed the namespace of the WPCS core classes

## [0.4.1](https://github.com/infinum/coding-standards-wp/compare/0.3.1...0.4.1) - 2018-11-15

### Added
- Silenced previously excluded sniffs to avoid loading the entire `WordPress` ruleset
- Silenced `WordPress.Arrays.ArrayIndentation` to avoid it clashing with Generic indentation sniff

### Removed
- Fixed multiple alignment sniff issue

### Changed
- Reorganized sniff rules
- Raised the minimum supported PHP version to PHP 7.1

## [0.4.0](https://github.com/infinum/coding-standards-wp/compare/0.3.1...0.4.0) - 2018-10-24

### Added
- Unit tests - the basic setup is taken from https://github.com/WPTRT/WPThemeReview/
- Ignore rule about enqueueing scripts in the footer
- .gitattributes file for release purposes
- Added internal ruleset for writing additional sniffs
- Added rule about alignment of assignment operators (see this [customizable property](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Customisable-Sniff-Properties#genericformattingmultiplestatementalignment))
- Added a rule about function parameters and indentation
- Ignore unused parameters in functions on account on hooks
- Added a rule about private methods and properties - they MUST NOT be prefixed with an underscore
- Added PHPCompatibilityWP ruleset for checking a cross-version PHP compatibility (from PHP 7.0 onwards)
- Added detailed CHANGELOG
- Updated readme
- Added autoload for loading the standards
- Updated composer.json
  - added different requirements
  - added unit test packages
  - moved phpcodesniffer-composer-installer to suggested package
  - updated scripts
- Added .travis.yml for automatic checks on new sniff addition

### Removed
- Removed ruleset.xml from the root of the standards
- Removed WordPress.Arrays.MultipleStatementAlignment exclusion rule

### Fixed
- Minor coding standard fix in the DisallowDoShortcode sniff
- Fix the array indentation issue

## [0.3.1](https://github.com/infinum/coding-standards-wp/compare/0.3.0...0.3.1) - 2018-07-27

### Changed
- Set the WPCS dependency to >= 1.0.0

## [0.3.0](https://github.com/infinum/coding-standards-wp/compare/0.2.8...0.3.0) - 2018-07-26

### Added
- Added minimum_supported_wp_version check - v4.7 is set as the minimum

### Changed
- Added additional rules to ruleset
- Fix the exclude patterns
- Change the VIP ruleset to WP native one since VIP is being deprecated upstream

### Removed
- Removed OnlyClassInFile sniff as it's pulled from the upstream

### Fixed
- Updated the DisallowDoShortcodeSniff to match phpcs 3.0.0 and latest WPCS

## [0.2.8](https://github.com/infinum/coding-standards-wp/compare/0.2.6...0.2.8) - 2018-06-21

### Added
- More files to the .gitignore file
- Added WordPress.VIP.PostsPerPage rule with 1000 posts limit
- Added WordPress.WhiteSpace.PrecisionAlignment to the exclude list
- Added WordPress.Arrays.MultipleStatementAlignment to the exclude list
- Added WordPress.Arrays.ArrayIndentation to the ruleset
- License file
- Update WPCS dependency to 0.14.0

### Changed
- Changed from GPLv2 to MIT license
- Updated readme - minor improvements and added credits

### Removed
- Removed PSR1.Classes.ClassDeclaration exclusion

## [0.2.6](https://github.com/infinum/coding-standards-wp/compare/0.2.5...0.2.6) - 2017-10-28

### Fixed
- Composer installed paths

## [0.2.5](https://github.com/infinum/coding-standards-wp/compare/0.2.4...0.2.5) - 2017-10-28

### Changed
- Updated readme and added more explanations for usage in IDE's
- Updated the WPCS dependency to 0.13.0

### Removed
- Removed default report since it conflicted with VSCode

## [0.2.4](https://github.com/infinum/coding-standards-wp/compare/0.2.3...0.2.4) - 2017-10-28

### Fixed
- Composer scripts - fixed installed paths set

## [0.2.3](https://github.com/infinum/coding-standards-wp/compare/0.2.2...0.2.3) - 2017-09-19

### Added
- Added `tab-width` rule (2 spaces default)

### Removed
- Removed prefix check, since we started using namespaces and OOP instead of procedural php and global namespace

### Changed
- Corrected the indentation in the ruleset

## [0.2.2](https://github.com/infinum/coding-standards-wp/compare/0.2.1...0.2.2) - 2017-07-25

### Changed
- Updated DisallowDoShortcodeSniff regex - from multiline check to just case insensitive check
- Increased dependencu on WPCS 0.12.0

## [0.2.1](https://github.com/infinum/coding-standards-wp/compare/0.2.1...main) - 2017-07-18

Initial release
