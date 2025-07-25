<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetSerializerInterface;
use Dullahan\Asset\Port\Presentation\AssetServiceInterface;
use Dullahan\Main\Service\CacheService;
use Dullahan\Main\Service\EditorJsService;
use Dullahan\Object\Adapter\Symfony\Domain\Reader\FieldReader;
use Dullahan\Object\Domain\Contract\InheritanceAwareInterface;
use Dullahan\Object\Port\Domain\EntityServiceInterface;
use Dullahan\Object\Port\Domain\EntityValidationInterface;
use Dullahan\Object\Port\Interface\EntityRepositoryInterface;
use Dullahan\Object\Presentation\Event\Transport\OwnershipCheck;
use Dullahan\Object\Presentation\Event\Transport\PostCreate;
use Dullahan\Object\Presentation\Event\Transport\PostRemove;
use Dullahan\Object\Presentation\Event\Transport\PostUpdate;
use Dullahan\Object\Presentation\Event\Transport\PostValidationCreate;
use Dullahan\Object\Presentation\Event\Transport\PostValidationUpdate;
use Dullahan\Object\Presentation\Event\Transport\PreCreate;
use Dullahan\Object\Presentation\Event\Transport\PreRemove;
use Dullahan\Object\Presentation\Event\Transport\PreUpdate;
use Dullahan\Object\Presentation\Event\Transport\Retrieval;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Application\UserServiceInterface;
use Dullahan\User\Port\Domain\ManageableInterface;
use Dullahan\User\Port\Domain\OwnerlessManageableInterface;
use Dullahan\User\Port\Domain\TransferableOwnerManageableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityUtilService implements EntityServiceInterface
{
    use Trait\EntityUtil\EntityUtilSetterTrait;
    use Trait\EntityUtil\EntityUtilHelperTrait;
    use Trait\EntityUtil\EntityUtilSerializeTrait;
    use Trait\EntityUtil\EntityUtilRemoveTrait;

    protected ?User $user = null;
    protected bool $inherit = true;
    protected bool $validateOwner = true;

    public function __construct(
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected EventDispatcherInterface $eventDispatcher,
        protected EntityValidationInterface $validationService,
        protected AssetServiceInterface $assetService,
        protected EmptyIndicatorService $emptyIndicatorService,
        protected CacheService $cacheService,
        protected EditorJsService $editorJsService,
        protected AssetSerializerInterface $assetSerializer,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws \Exception
     */
    public function get(string $class, int $id): object
    {
        $repo = $this->getRepository($class);
        /** @var T|null $entity */
        $entity = $repo->find($id);
        if (!$entity) {
            throw new \Exception('Entity not found', Response::HTTP_NOT_FOUND);
        }
        $this->eventDispatcher->dispatch(new Retrieval($entity));

        return $entity;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return EntityRepositoryInterface<T>
     */
    public function getRepository(string $class): EntityRepositoryInterface
    {
        if (!class_exists($class)) {
            throw new \Exception(sprintf("Class %s doesn't exist", $class), 400);
        }

        /** @var EntityRepositoryInterface<T> $repository */
        $repository = $this->em->getRepository($class);

        return $repository;
    }

    public function enableOwnershipCheck(): void
    {
        $this->validateOwner = true;
    }

    public function disableOwnershipCheck(): void
    {
        $this->validateOwner = false;
    }

    /**
     * @param array<string, mixed>|null $dataSet
     *
     * @return array<string, mixed>
     */
    public function serialize(
        object $entity,
        ?array $dataSet = null,
        bool $inherit = true
    ): array {
        // @TODO fix this with special one time class for serialization - encapsulate logic
        $tmp = $this->inherit;
        $this->inherit = $inherit;

        $cache = $this->cacheService->getCache();
        $serializedKey = $this->cacheService->getEntitySerializedCacheKey($entity, $this->inherit);
        $serializedItem = $cache->getItem($serializedKey);
        if ($serializedItem->isHit()) {
            $serialized = json_decode($serializedItem->get(), true);
            if ($serialized) {
                $res = $this->retrieveNecessaryOnly($serialized, $dataSet ?? []);
                $this->inherit = $tmp;

                return $res;
            }
        }

        $definition = $this->getEntityDefinition($entity);
        $serialized = $this->getFields($entity, $definition);

        if (method_exists($entity, 'getId') && $entity->getId()) {
            $serializedItem->set(json_encode($serialized))->expiresAfter(60 * 60);
            $cache->save($serializedItem);
        }

        $result = $this->retrieveNecessaryOnly($serialized, $dataSet ?? []);
        $this->inherit = $tmp;

        return $result;
    }

    /**
     * @return array<mixed>
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getEntityDefinition(object $entity): array
    {
        $cache = $this->cacheService->getCache();
        $item = $cache->getItem($this->cacheService->getEntityDefinitionCacheKey($entity::class));
        if ($item->isHit()) {
            $definition = json_decode($item->get(), true);
        } else {
            $definition = json_decode(
                json_encode(
                    (new FieldReader($entity, $this->cacheService))->getFields($this->getEntityTrueClass($entity))
                ) ?: '',
                true
            );
            $item->set(json_encode($definition))->expiresAfter(60 * 60 * 24);
            $cache->save($item);
        }

        return $definition;
    }

    /**
     *  @template T of object
     *
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $payload
     *
     * @return T
     */
    public function create(string $class, array $payload, bool $flush = true): object
    {
        /**
         * @var (ManageableInterface&T)|(OwnerlessManageableInterface&T)|(TransferableOwnerManageableInterface&T) $entity
         */
        $entity = $this->generate($class);
        $this->validationService->handlePreCreateValidation($entity, $payload);
        $pre = new PreCreate($entity, $payload);
        $this->eventDispatcher->dispatch($pre);
        $payload = $pre->getPayload();

        $definition = $this->getEntityDefinition($entity);
        $this->fillEntity($class, $entity, $payload, $definition);

        $this->eventDispatcher->dispatch(new OwnershipCheck($pre));
        if ($entity instanceof ManageableInterface) {
            $entity->setOwner($this->user ?? $this->userService->getLoggedInUser());
        }

        $this->eventDispatcher->dispatch(new PostValidationCreate($entity));
        $this->em->persist($entity);
        if ($flush) {
            $this->em->flush();
        }

        if ($entity instanceof InheritanceAwareInterface && $entity->getParent()) {
            $entity->getParent()->addChild($entity);
        }
        $this->emptyIndicatorService->setEmptyIndicators($entity, $payload);

        $this->eventDispatcher->dispatch(new PostCreate($entity, $payload));
        // Clear all cached related entities - otherwise we might get them but without newly created entity
        $this->clearRelatedCache($entity, $definition);

        return $entity;
    }

    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     */
    public function update(string $class, int $id, array $payload, bool $persist = true): object
    {
        $entity = $this->get($class, $id);
        $this->validationService->handlePreUpdateValidation($entity, $payload, $this->validateOwner);
        /** @var ManageableInterface|OwnerlessManageableInterface|TransferableOwnerManageableInterface $entity */
        $pre = new PreUpdate($entity, $payload);
        $this->eventDispatcher->dispatch($pre);
        $payload = $pre->getPayload();

        $this->fillEntity($class, $entity, $payload, $this->getEntityDefinition($entity));

        $this->eventDispatcher->dispatch(new OwnershipCheck($pre));
        $this->validateOwnership($entity);

        $this->eventDispatcher->dispatch(new PostValidationUpdate($entity));
        $this->em->persist($entity);
        if ($persist) {
            $this->em->flush();
        }

        if ($entity instanceof InheritanceAwareInterface && $entity->getParent()) {
            $entity->getParent()->addChild($entity);
        }
        $this->emptyIndicatorService->setEmptyIndicators($entity, $payload);

        $this->eventDispatcher->dispatch(new PostUpdate($entity, $payload));
        $this->removeEntityCache($entity);

        return $entity;
    }

    /**
     * @param class-string $class
     *
     * @throws \Exception
     */
    public function remove(string $class, int $id): void
    {
        $entity = $this->get($class, $id);
        if (
            !$entity instanceof ManageableInterface
            && !$entity instanceof OwnerlessManageableInterface
            && !$entity instanceof TransferableOwnerManageableInterface
        ) {
            throw new \Exception('Chosen entity cannot be deleted', 400);
        }

        $pre = new PreRemove($entity);
        $this->eventDispatcher->dispatch($pre);
        $this->eventDispatcher->dispatch(new OwnershipCheck($pre));
        $this->validateOwnership($entity);

        if ($entity instanceof InheritanceAwareInterface) {
            $id = $entity->getId();
            $this->removeParent($entity);
            $this->emptyIndicatorService->removeEmptyIndicators($entity::class, (int) $id);
        } else {
            $this->em->remove($entity);
            $this->em->flush();
        }

        $this->eventDispatcher->dispatch(new PostRemove($entity));
        // Clear all cached related entities - otherwise we might get them but with just deleted entity
        $this->clearRelatedCache($entity, $this->getEntityDefinition($entity));
    }
}
