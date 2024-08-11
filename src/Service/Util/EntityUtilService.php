<?php

declare(strict_types=1);

namespace Dullahan\Service\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Dullahan\Contract\AssetManager\AssetManagerInterface;
use Dullahan\Contract\AssetManager\AssetSerializerInterface;
use Dullahan\Contract\InheritanceAwareInterface;
use Dullahan\Contract\ManageableInterface;
use Dullahan\Contract\Marker\UserServiceInterface;
use Dullahan\Contract\OwnerlessManageableInterface;
use Dullahan\Contract\TransferableOwnerManageableInterface;
use Dullahan\Entity\AssetPointer;
use Dullahan\Entity\User;
use Dullahan\Event\Entity\OwnershipCheck;
use Dullahan\Event\Entity\PostCreate;
use Dullahan\Event\Entity\PostRemove;
use Dullahan\Event\Entity\PostUpdate;
use Dullahan\Event\Entity\PostValidationCreate;
use Dullahan\Event\Entity\PostValidationUpdate;
use Dullahan\Event\Entity\PreCreate;
use Dullahan\Event\Entity\PreRemove;
use Dullahan\Event\Entity\PreUpdate;
use Dullahan\Event\Entity\Retrieval;
use Dullahan\Reader\FieldReader;
use Dullahan\Service\CacheService;
use Dullahan\Service\EditorJsService;
use Dullahan\Service\EmptyIndicatorService;
use Dullahan\Service\ValidationService;
use Dullahan\Trait\EntityUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityUtilService
{
    use EntityUtil\EntityUtilSetterTrait;
    use EntityUtil\EntityUtilHelperTrait;
    use EntityUtil\EntityUtilSerializeTrait;
    use EntityUtil\EntityUtilRemoveTrait;

    protected ?User $user = null;
    protected bool $inherit = true;
    protected bool $validateOwner = true;

    public function __construct(
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected EventDispatcherInterface $eventDispatcher,
        protected ValidationService $validationService,
        protected AssetManagerInterface $assetManager,
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
     * @return EntityRepository<T>
     */
    public function getRepository(string $class): EntityRepository
    {
        if (!class_exists($class)) {
            throw new \Exception(sprintf("Class %s doesn't exist", $class), 400);
        }

        return $this->em->getRepository($class);
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
            $this->assetManager->flush();
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
            $this->assetManager->flush();
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
            $this->assetManager->flush();
        }

        $this->eventDispatcher->dispatch(new PostRemove($entity));
        // Clear all cached related entities - otherwise we might get them but with just deleted entity
        $this->clearRelatedCache($entity, $this->getEntityDefinition($entity));
    }

    public function removeFromThumbnails(AssetPointer $pointer): void
    {
        foreach ($pointer->getThumbnailPointers() as $thumbnailPointer) {
            $thumbnail = $thumbnailPointer->getThumbnail();
            if (!$thumbnail) {
                continue;
            }
            $thumbnail->removeAssetPointer($thumbnailPointer);
            if ($thumbnail->getAssetPointers()->isEmpty()) {
                $this->em->remove($thumbnail);
            }
        }
    }
}
