<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Application\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineDqlCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('doctrine')) {
            throw new \LogicException('DoctrineBundle is not registered, but it is required by DullahanBundle');
        }

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'dql' => [
                    'string_functions' => [
                        'replace' => 'DoctrineExtensions\Query\Mysql\Replace',
                    ],
                ],
            ],
        ]);

        // We also have to add it manually as doctrine doesn't recognize custom function added during compile time
        $definition = $container->getDefinition('doctrine.orm.configuration');
        $definition->addMethodCall('addCustomStringFunction', [
            'replace',
            'DoctrineExtensions\Query\Mysql\Replace',
        ]);
    }
}
