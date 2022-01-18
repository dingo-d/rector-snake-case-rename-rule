<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20220117\Symfony\Component\Console\DependencyInjection;

use ECSPrefix20220117\Symfony\Component\Console\Command\Command;
use ECSPrefix20220117\Symfony\Component\Console\Command\LazyCommand;
use ECSPrefix20220117\Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\ContainerBuilder;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\Reference;
use ECSPrefix20220117\Symfony\Component\DependencyInjection\TypedReference;
/**
 * Registers console commands.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class AddConsoleCommandPass implements \ECSPrefix20220117\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(\ECSPrefix20220117\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $commandServices = $container->findTaggedServiceIds('console.command', \true);
        $lazyCommandMap = [];
        $lazyCommandRefs = [];
        $serviceIds = [];
        foreach ($commandServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->addTag('container.no_preload');
            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            if (isset($tags[0]['command'])) {
                $aliases = $tags[0]['command'];
            } else {
                if (!($r = $container->getReflectionClass($class))) {
                    throw new \ECSPrefix20220117\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }
                if (!$r->isSubclassOf(\ECSPrefix20220117\Symfony\Component\Console\Command\Command::class)) {
                    throw new \ECSPrefix20220117\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The service "%s" tagged "%s" must be a subclass of "%s".', $id, 'console.command', \ECSPrefix20220117\Symfony\Component\Console\Command\Command::class));
                }
                $aliases = $class::getDefaultName();
            }
            $aliases = \explode('|', $aliases ?? '');
            $commandName = \array_shift($aliases);
            if ($isHidden = '' === $commandName) {
                $commandName = \array_shift($aliases);
            }
            if (null === $commandName) {
                if (!$definition->isPublic() || $definition->isPrivate() || $definition->hasTag('container.private')) {
                    $commandId = 'console.command.public_alias.' . $id;
                    $container->setAlias($commandId, $id)->setPublic(\true);
                    $id = $commandId;
                }
                $serviceIds[] = $id;
                continue;
            }
            $description = $tags[0]['description'] ?? null;
            unset($tags[0]);
            $lazyCommandMap[$commandName] = $id;
            $lazyCommandRefs[$id] = new \ECSPrefix20220117\Symfony\Component\DependencyInjection\TypedReference($id, $class);
            foreach ($aliases as $alias) {
                $lazyCommandMap[$alias] = $id;
            }
            foreach ($tags as $tag) {
                if (isset($tag['command'])) {
                    $aliases[] = $tag['command'];
                    $lazyCommandMap[$tag['command']] = $id;
                }
                $description = $description ?? $tag['description'] ?? null;
            }
            $definition->addMethodCall('setName', [$commandName]);
            if ($aliases) {
                $definition->addMethodCall('setAliases', [$aliases]);
            }
            if ($isHidden) {
                $definition->addMethodCall('setHidden', [\true]);
            }
            if (!$description) {
                if (!($r = $container->getReflectionClass($class))) {
                    throw new \ECSPrefix20220117\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }
                if (!$r->isSubclassOf(\ECSPrefix20220117\Symfony\Component\Console\Command\Command::class)) {
                    throw new \ECSPrefix20220117\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('The service "%s" tagged "%s" must be a subclass of "%s".', $id, 'console.command', \ECSPrefix20220117\Symfony\Component\Console\Command\Command::class));
                }
                $description = $class::getDefaultDescription();
            }
            if ($description) {
                $definition->addMethodCall('setDescription', [$description]);
                $container->register('.' . $id . '.lazy', \ECSPrefix20220117\Symfony\Component\Console\Command\LazyCommand::class)->setArguments([$commandName, $aliases, $description, $isHidden, new \ECSPrefix20220117\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument($lazyCommandRefs[$id])]);
                $lazyCommandRefs[$id] = new \ECSPrefix20220117\Symfony\Component\DependencyInjection\Reference('.' . $id . '.lazy');
            }
        }
        $container->register('console.command_loader', \ECSPrefix20220117\Symfony\Component\Console\CommandLoader\ContainerCommandLoader::class)->setPublic(\true)->addTag('container.no_preload')->setArguments([\ECSPrefix20220117\Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass::register($container, $lazyCommandRefs), $lazyCommandMap]);
        $container->setParameter('console.command.ids', $serviceIds);
    }
}