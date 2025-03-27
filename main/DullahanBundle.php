<?php

declare(strict_types=1);

namespace Dullahan\Main;

use Dullahan\Main\DependencyInjection\DoctrineDqlCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DullahanBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode() // @phpstan-ignore method.notFound
            ->children()
                ->arrayNode('projects')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Register the Compiler Pass to modify Doctrine config
        $container->addCompilerPass(new DoctrineDqlCompilerPass());
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array<string, mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services.yaml');
        $builder->getDefinition('Dullahan\Main\Service\ProjectManagerService')
            ->setArgument('$projects', $config['projects'])
        ;
    }
}
