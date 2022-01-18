<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use Utils\Rector\Rector\Renaming\RenameClassNameRector;

return static function (ContainerConfigurator $containerConfigurator): void {
	// Get parameters.
	$parameters = $containerConfigurator->parameters();

	// Paths to refactor; solid alternative to CLI arguments.
	$parameters->set(Option::PATHS, [__DIR__ . '/src/admin/class-test-class-name.php']);

	$parameters->set(
		Option::AUTOLOAD_PATHS,
		[
			__DIR__ . '/vendor/squizlabs/php_codesniffer/autoload.php',
			__DIR__ . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
			__DIR__ . '/vendor/php-stubs/acf-pro-stubs/acf-pro-stubs.php',
		]
	);

	$containerConfigurator->import(SetList::PSR_12);

	$parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

	// Auto import fully qualified class names? [default: false].
	$parameters->set(Option::AUTO_IMPORT_NAMES, true);

	// Skip root namespace classes, like \DateTime or \Exception [default: true].
	$parameters->set(Option::IMPORT_SHORT_CLASSES, false);

	$parameters->set(Option::APPLY_AUTO_IMPORT_NAMES_ON_CHANGED_FILES_ONLY, true);

	// Path to phpstan with extensions, that PHPStan in Rector uses to determine types.
	$parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan.neon.dist');

	$parameters->set(Option::SKIP, [
		RemoveUnusedPromotedPropertyRector::class, // PHP8 Feature.
	]);

	// Register single rule.
	$services = $containerConfigurator->services();

	// Change root namespace.
	$services->set(RenameNamespaceRector::class)
		->configure([
			'Test' => 'TestNamespace',
		]);

	// Change class and file name of the class.
	$services->set(RenameClassNameRector::class);
};
