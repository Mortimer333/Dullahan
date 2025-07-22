<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Domain\EntityHydrationInterface;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\UpdateEntity;

class UpdateEntityFunctor
{
    public function __construct(
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected EntityHydrationInterface $entityHydrator,
    ) {
    }

    public function __invoke(UpdateEntity $event): IdentityAwareInterface
    {
        $entity = $event->entity;
        $definition = $this->entityDefinitionManager->getEntityDefinition($entity);
        if (!$definition) {
            throw new InvalidEntityException(
                sprintf('Entity %s is missing definition', $entity::class),
            );
        }
        $this->entityHydrator->hydrate($entity::class, $entity, $event->payload, $definition);

        return $entity;
    }
}
