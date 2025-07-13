<?php

declare(strict_types=1);

namespace Dullahan\Entity\Application;

use Dullahan\Entity\Domain\Enum\AccessTypeEnum;
use Dullahan\Entity\Domain\Enum\EntityCacheCaseEnum;
use Dullahan\Entity\Domain\Enum\EntityCacheCastEnum;
use Dullahan\Entity\Domain\Exception\EntityCreationFailedException;
use Dullahan\Entity\Domain\Exception\EntityNotAuthorizedException;
use Dullahan\Entity\Domain\Exception\EntityNotFoundException;
use Dullahan\Entity\Domain\Exception\EntityValidationException;
use Dullahan\Entity\Port\Application\EntityCacheManagerInterface;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Application\EntityPersistManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Application\EntitySerializerInterface;
use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\Entity\Presentation\Event\Transport;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Application\UserServiceInterface;
use ICanBoogie\Inflector;

/**
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 */
class EntityManagerFacade
implements EntityPersistManagerInterface, EntityRetrievalManagerInterface, EntityDefinitionManagerInterface,
    EntityCacheManagerInterface, EntitySerializerInterface
{
    protected Inflector $inflector;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected UserServiceInterface $userManagerService,
        protected EntityCacheServiceInterface $entityCacheService,
    ) {
        $this->inflector = Inflector::get('en');
    }

    public function get(string $class, int $id): ?IdentityAwareInterface
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::GET->value);
        $repository = $this->eventDispatcher->dispatch(new Transport\GetEntityRepository($class))->repository;
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
        return $this->eventDispatcher->dispatch(new Transport\GetEntityRepository($class))->repository;
    }

    /**
     * @TODO I quite dislike how inherit is handled here. We might want to move it to separate bundle
     *      and with current implementation it would be a nightmare. We probably should allow for setting one context
     *      at the start and then passing it the rest of the events
     */
    public function serialize(object $entity, ?array $dataSet = null, bool $inherit = true): ?array
    {
        /** @var Transport\GetEntityCache<array<mixed>> $eventGetCache */
        $eventGetCache = new Transport\GetEntityCache(
            $this->entityCacheService->getEntitySerializedCacheKey($entity, $inherit),
            EntityCacheCaseEnum::SERIALIZATION->value,
            EntityCacheCastEnum::JSON_ARRAY->value,
        );
        $eventGetCache->context->setProperty('dataSet', $dataSet);
        $eventGetCache->context->setProperty('entity', $entity);
        $eventGetCache->context->setProperty('inherit', $inherit);
        $eventGetCache = $this->eventDispatcher->dispatch($eventGetCache);
        if ($eventGetCache->isHit) {
            return $eventGetCache->cached;
        }

        $definition = $this->getEntityDefinition($entity);
        if (!$definition) {
            return null;
        }

        $eventSerialize = new Transport\SerializeEntity($entity, $definition, $inherit);
        $eventSerialize->context->setContext($eventGetCache->context->getContext());

        $serialized = $this->eventDispatcher->dispatch($eventSerialize)->serialized;
        if (!$serialized) {
            return null;
        }

        if ($cache = json_encode($serialized)) {
            $eventGetCache = new Transport\CacheEntity(
                $eventGetCache->key,
                $cache,
                60 * 60 * 24,
                EntityCacheCaseEnum::SERIALIZATION->value,
            );
        }
        $eventSerialize->context->setContext($eventSerialize->context->getContext());
        $this->eventDispatcher->dispatch($eventGetCache);

        $eventStrip = new Transport\StripSerializedEntity($entity, $serialized, $dataSet);
        $eventStrip->context->setContext($eventSerialize->context->getContext());

        return $this->eventDispatcher->dispatch($eventStrip)->serialized;
    }

    public function getEntityDefinition(object $entity): ?array
    {
        /** @var Transport\GetEntityCache<SerializedEntity> $eventGetCache */
        $eventGetCache = new Transport\GetEntityCache(
            $this->entityCacheService->getEntityDefinitionCacheKey($entity::class),
            EntityCacheCaseEnum::DEFINITION->value,
            EntityCacheCastEnum::JSON_ARRAY->value,
        );
        if ($eventGetCache->isHit) {
            return $eventGetCache->get();
        }

        $definition = $this->eventDispatcher->dispatch(new Transport\GetEntityDefinition($entity))->definition;
        if (!is_null($definition) && $cache = json_encode($definition)) {
            $this->eventDispatcher->dispatch(new Transport\CacheEntity(
                $eventGetCache->key,
                $cache,
                60 * 60 * 24,
                EntityCacheCaseEnum::DEFINITION->value,
            ));
        }

        return $definition;
    }

    public function getEntityTrueClass(object $entity): ?string
    {
        return $this->eventDispatcher->dispatch(new Transport\GetEntityTrueClass($entity))->className;
    }

    public function create(string $class, array $payload, bool $flush = true): IdentityAwareInterface
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
        // We are requesting ownership check after entity was created to make sure that it is correctly assigned
        // and not sneakily assigned to another/wrong/unauthorized user
        $this->dispatchOwnerVerification($entity);

        return $entity;
    }

    public function update(string $class, int $id, array $payload, bool $flush = true): IdentityAwareInterface
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::UPDATE->value);
        $entity = $this->get($class, $id);
        if (!$entity) {
            throw new EntityNotFoundException('Entity was not found');
        }

        // [Double owner verifications]
        //  Firstly we make sure that entity can be changed by this user
        //  Secondly we verify if the ownership wasn't wrongly changed after the update
        $this->dispatchOwnerVerification($entity);
        $validation = $this->eventDispatcher->dispatch(new Transport\ValidateUpdateEntity($entity, $payload));
        if (!$validation->isValid) {
            throw new EntityValidationException('Entity update has failed');
        }
        $this->dispatchOwnerVerification($entity);

        return $this->eventDispatcher->dispatch(
            new Transport\UpdateEntity($entity, $validation->payload, $flush)
        )->entity;
    }

    public function remove(string $class, int $id, bool $flush = true): bool
    {
        $this->dispatchAccessVerification($class, AccessTypeEnum::DELETE->value);
        $entity = $this->get($class, $id);
        if (!$entity) {
            throw new EntityNotFoundException('Entity was not found');
        }

        $this->dispatchOwnerVerification($entity);
        $this->eventDispatcher->dispatch(new Transport\RemoveEntity($entity, $flush));

        return true;
    }

    /**
     * @TODO
     */
    public function removeCacheById(string $class, int $id): void
    {
        $this->eventDispatcher->dispatch(new Transport\CacheRemoveEntityId($class, $id));
    }

    /**
     * @TODO
     */
    public function removeCache(object $entity): void
    {
        $this->eventDispatcher->dispatch(new Transport\CacheRemoveEntity($entity));
    }

    public function removeRelatedCache(object $entity, array $definition): void
    {
        $this->eventDispatcher->dispatch(new Transport\CacheRemoveRelated($entity, $definition));
    }

    /**
     * @param array<mixed>|string $definition
     */
    public function pluralize(array|string $definition, string $name): string
    {
        if (!is_array($definition) || !isset($definition['plural'])) {
            return $this->inflector->pluralize($name);
        }

        return $definition['plural'];
    }

    /**
     * @param array<mixed>|string $definition
     */
    public function singularize(array|string $definition, string $name): string
    {
        if (!is_array($definition) || !isset($definition['singular'])) {
            return $this->inflector->singularize($name);
        }

        return $definition['singular'];
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
