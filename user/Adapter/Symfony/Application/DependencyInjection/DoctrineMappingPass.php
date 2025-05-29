<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Symfony\Application\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineMappingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('doctrine')) {
            throw new \LogicException('DoctrineBundle is not registered, but it is required by DullahanUserBundle');
        }

        $namespace = 'Dullahan\\User\\Domain\\Entity';
        $path = realpath(dirname(__FILE__) . '/../../../../Domain/Entity');
        $mappingDriver = DoctrineOrmMappingsPass::createAttributeMappingDriver(
            [$namespace],
            [$path],
        );

        $mappingDriver->process($container);
    }
}
