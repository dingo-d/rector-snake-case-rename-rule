<?php

declare (strict_types=1);
namespace RectorPrefix20220117;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $additionalComposerExtensions = [new \Rector\Composer\ValueObject\RenamePackage('typo3-ter/social_auth', 'kalypso63/social_auth')];
    $allComposerExtensions = \array_merge($composerExtensions, $additionalComposerExtensions);
    $services->set(\Rector\Composer\Rector\RenamePackageComposerRector::class)->configure($allComposerExtensions);
};