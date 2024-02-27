<?php

declare(strict_types=1);

namespace Dullahan;

use Dullahan\Service\Util\BinUtilService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DullahanBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('projects')
                ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')->isRequired()->end()
                            ->scalarNode('class')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array<string, mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder $containerBuilder
    ): void {
        $containerConfigurator->import('../config/services.yaml');
    }
}
