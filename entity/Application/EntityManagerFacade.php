<?php

declare(strict_types=1);

namespace Dullahan\Entity\Application;

use Dullahan\Entity\Domain\Enum\AccessTypeEnum;
use Dullahan\Entity\Domain\Exception\EntityCreationFailedException;
use Dullahan\Entity\Domain\Exception\EntityNotAuthorizedException;
use Dullahan\Entity\Domain\Exception\EntityNotFoundException;
use Dullahan\Entity\Domain\Exception\EntityValidationException;
use Dullahan\Entity\Port\Application\EntityCacheManagerInterface;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Application\EntityPersistManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Application\EntitySerializerInterface;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\Entity\Presentation\Event\Transport;
use Dullahan\Entity\Presentation\Event\Transport\GetEntityRepository;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Application\UserServiceInterface;

class EntityManagerFacade
implements EntityPersistManagerInterface, EntityRetrievalManagerInterface, EntityDefinitionManagerInterface,
    EntityCacheManagerInterface, EntitySerializerInterface
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected UserServiceInterface $userManagerService,
    ) {
    }

    public function get(string $class, int $id): ?object
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::GET->value);
        $repository = $this->eventDispatcher->dispatch(new GetEntityRepository($class))->repository;
        if (!$repository) {
            return null;
        }

        $entity = $this->eventDispatcher->dispatch(new Transport\GetEntity($class, $id, $repository))->entity;
        if ($entity) {
            $this->dispatchOwnerVerification($entity);
        }

        return $entity;
    }

    public function getRepository(string $class): ?EntityRepositoryInterface
    {
        return $this->eventDispatcher->dispatch(new GetEntityRepository($class))->repository;
    }

    /**
     * @TODO I really dislike how inherit is handled here. We might want to move it to separate bundle
     *      and with current implementation it would be a nightmare.
     */
    public function serialize(object $entity, ?array $dataSet = null, bool $inherit = true): ?array
    {
        $definition = $this->getEntityDefinition($entity);
        if (!$definition) {
            return null;
        }

        $eventRegister = new Transport\RegisterEntityNormalizer($entity);
        $eventRegister->context->setProperty('inherit', $inherit);

        // @TODO maybe additionally allow for settings default normalizers in configuration? There may be cases when it
        //      depends on the entity but this seems non-intuitive to only allow adding them by event
        $normalizers = $this->eventDispatcher->dispatch($eventRegister)->getSortedNormalizers();

        $eventSerialize = new Transport\SerializeEntity($entity, $definition, $normalizers, $inherit);
        $eventSerialize->context->setContext($eventRegister->context->getContext());

        $serialized = $this->eventDispatcher->dispatch($eventSerialize)->serialized;
        if (!$serialized) {
            return null;
        }

        // @TODO implement Transport\CacheSerializedEntity
        $this->eventDispatcher->dispatch(new Transport\CacheSerializedEntity($entity, $serialized, $inherit));

        $eventStrip = new Transport\StripSerializedEntity($entity, $serialized, $dataSet);
        $eventStrip->context->setContext($eventSerialize->context->getContext());

        return $this->eventDispatcher->dispatch($eventStrip)->serialized;
    }

    public function getEntityDefinition(object $entity): ?array
    {
        $definition = $this->eventDispatcher->dispatch(new Transport\GetEntityDefinition($entity))->definition;
        // @TODO implement
        // @TODO additional caching shouldn't happened when cache was retrieved
        if ($definition) {
            $this->eventDispatcher->dispatch(new Transport\CacheEntityDefinition($entity, $definition));
        }

        return $definition;
    }

    public function getEntityTrueClass(object $entity): ?string
    {
        return $this->eventDispatcher->dispatch(new Transport\GetEntityTrueClass($entity))->className;
    }

    public function create(string $class, array $payload, bool $flush = true): object
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::CREATE->value);
        $validation = $this->eventDispatcher->dispatch(new Transport\ValidateCreateEntity($class, $payload));
        if (!$validation->isValid) {
            throw new EntityValidationException('Entity creation has failed');
        }

        $entity = $this->eventDispatcher->dispatch(
            new Transport\CreateEntity($class, $validation->payload, $flush),
        )->entity;
        if (!$entity) {
            throw new EntityCreationFailedException('Entity creation was not handled');
        }

        return $this->eventDispatcher->dispatch(new Transport\PostCreateEntity($entity))->entity;
    }

    public function update(string $class, int $id, array $payload, bool $flush = true): object
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::UPDATE->value);
        $entity = $this->get($class, $id);
        if (!$entity) {
            throw new EntityNotFoundException('Entity was not found');
        }

        $validation = $this->eventDispatcher->dispatch(new Transport\ValidateUpdateEntity($entity, $payload));
        if (!$validation->isValid) {
            throw new EntityValidationException('Entity update has failed');
        }

        return $this->eventDispatcher->dispatch(new Transport\PostUpdateEntity(
            $this->eventDispatcher->dispatch(new Transport\UpdateEntity($entity, $validation->payload, $flush))->entity,
        ))->entity;
    }

    public function delete(string $class, int $id, bool $flush = true): bool
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::DELETE->value);
        $entity = $this->get($class, $id);
        if (!$entity) {
            throw new EntityNotFoundException('Entity was not found');
        }

        $this->eventDispatcher->dispatch(new Transport\PostRemoveEntity(
            $this->eventDispatcher->dispatch(new Transport\RemoveEntity($entity, $flush))->entity,
        ));

        return true;
    }

    public function removeCacheById(string $class, int $id): void
    {
        $this->eventDispatcher->dispatch(new Transport\CacheRemoveEntityId($class, $id));
    }

    public function removeCache(object $entity): void
    {
        $this->eventDispatcher->dispatch(new Transport\CacheRemoveEntity($entity));
    }

    public function removeRelatedCache(object $entity, array $definition): void
    {
        $this->eventDispatcher->dispatch(new Transport\CacheRemoveRelated($entity, $definition));
    }

    /**
     * @return Transport\VerifyEntityOwnership<object>
     */
    protected function dispatchOwnerVerification(object $entity): Transport\VerifyEntityOwnership
    {
        $event = $this->eventDispatcher->dispatch(new Transport\VerifyEntityOwnership($entity, $this->tryToGetUser()));
        if (!$event->isValid) {
            throw new EntityNotAuthorizedException('Unauthorized owner of the entity');
        }

        return $event;
    }

    /**
     * @param class-string $className
     *
     * @return Transport\VerifyEntityAccess<object>
     */
    protected function dispatchAccessVerification(string $className, string $type): Transport\VerifyEntityAccess
    {
        $event = $this->eventDispatcher->dispatch(new Transport\VerifyEntityAccess(
            $className,
            $this->tryToGetUser(),
            $type,
        ));
        if (!$event->isValid) {
            throw new EntityNotAuthorizedException('Unauthorized access to entity');
        }

        return $event;
    }

    protected function tryToGetUser(): ?User
    {
        if ($this->userManagerService->isLoggedIn()) {
            return $this->userManagerService->getLoggedInUser();
        }

        return null;
    }
}
