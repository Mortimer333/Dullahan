<?php

declare(strict_types=1);

namespace Dullahan\Entity;

use Dullahan\Entity\Adapter\Symfony\Application\DependencyInjection\DoctrineMappingPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DullahanEntityBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode() // @phpstan-ignore method.notFound
            ->children()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                            ->scalarNode('prefix')->isRequired()->end()
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
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services-entity.yaml');
        $builder->getDefinition('Dullahan\Entity\Domain\Service\MappingsManagerService')
            ->setArgument('$mappings', $config['mappings'])
        ;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineMappingPass());
    }
}
