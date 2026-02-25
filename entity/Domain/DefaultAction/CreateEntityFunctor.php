<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Domain\EntityHydrationInterface;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Domain\ManageableInterface;
use Dullahan\Entity\Presentation\Event\Transport\CreateEntity;
use Dullahan\User\Port\Application\UserRetrieveServiceInterface;

class CreateEntityFunctor
{
    public function __construct(
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected EntityHydrationInterface $entityHydrator,
        protected UserRetrieveServiceInterface $userRetrieveService,
    ) {
    }

    public function __invoke(CreateEntity $event): IdentityAwareInterface
    {
        try {
            $entity = new $event->class();
        } catch (\Throwable) {
            $reflection = new \ReflectionClass($event->class);
            $entity = $reflection->newInstanceWithoutConstructor();
        }

        if (!$entity instanceof IdentityAwareInterface) {
            throw new InvalidEntityException(
                sprintf('Entity %s is not implementing %s', $event->class, IdentityAwareInterface::class),
            );
        }
        $definition = $this->entityDefinitionManager->getEntityDefinition($entity);
        if (!$definition) {
            throw new InvalidEntityException(
                sprintf('Entity %s is missing definition', $event->class),
            );
        }
        $this->entityHydrator->hydrate($event->class, $entity, $event->payload, $definition);

        if ($entity instanceof ManageableInterface) {
            $entity->setOwner($this->userRetrieveService->getLoggedInUser());
        }

        return $entity;
    }
}
