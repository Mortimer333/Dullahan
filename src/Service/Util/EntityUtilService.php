<?php

declare(strict_types=1);

namespace Dullahan\Service\Util;

use App\Event\Entity;
use App\Trait\EntityUtil;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Dullahan\Entity\AssetPointer;
use Dullahan\Entity\User;
use Dullahan\Enum\ProjectEnum;
use Dullahan\Reader\FieldReader;
use Dullahan\Service\AssetService;
use Dullahan\Service\CacheService;
use Dullahan\Service\EditorJsService;
use Dullahan\Service\EmptyIndicatorService;
use Dullahan\Service\UserService;
use Dullahan\Service\ValidationService;
use Dullahan\src\Contract\InheritanceAwareInterface;
use Dullahan\src\Contract\ManageableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityUtilService
{
    use \Dullahan\Trait\EntityUtil\EntityUtilSetterTrait;
    use \Dullahan\Trait\EntityUtil\EntityUtilHelperTrait;
    use \Dullahan\Trait\EntityUtil\EntityUtilSerializeTrait;
    use \Dullahan\Trait\EntityUtil\EntityUtilRemoveTrait;

    protected ?User $user = null;
    protected bool $inherit = true;
    protected bool $validateOwner = true;

    public function __construct(
        protected EntityManagerInterface $em,
        protected UserService $userService,
        protected EventDispatcherInterface $eventDispatcher,
        protected ValidationService $validationService,
        protected AssetService $assetService,
        protected EmptyIndicatorService $emptyIndicatorService,
        protected CacheService $cacheService,
        protected EditorJsService $editorJsService,
    ) {
    }

    public function urlSlugNamespaceToClassName(ProjectEnum $project, string $namespace): string
    {
        $context = match ($project) {
            ProjectEnum::Main => 'App\\Entity\\Main\\',
            ProjectEnum::Test => 'App\\EntityTest\\',
        };

        $namespace = str_replace('-', ' ', trim($namespace));
        $namespace = ucwords($namespace);
        $namespace = str_replace(' ', '\\', $namespace);

        return $context . $namespace;
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
            throw new \Exception('Entity not found', 400);
        }

        return $entity;
    }

    /**
     * @param class-string $class
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
        // @TODO fix this with special one time class for serialization
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
        /** @var ManageableInterface&T $entity */
        $entity = $this->generate($class);
        $this->validationService->handlePreCreateValidation($entity, $payload);
        $pre = new \Dullahan\Event\Entity\PreCreate($entity, $payload);
        $this->eventDispatcher->dispatch($pre);
        $payload = $pre->getPayload();

        $definition = $this->getEntityDefinition($entity);
        $this->fillEntity($class, $entity, $payload, $definition);
        $entity->setOwner($this->user ?? $this->userService->getLoggedInUser());

        $this->em->persist($entity);
        if ($flush) {
            $this->em->flush();
        }

        if ($entity instanceof InheritanceAwareInterface && $entity->getParent()) {
            $entity->getParent()->addChild($entity);
        }
        $this->emptyIndicatorService->setEmptyIndicators($entity, $payload);

        $this->eventDispatcher->dispatch(new \Dullahan\Event\Entity\PostCreate($entity, $payload));
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
        /** @var ManageableInterface $entity */
        $pre = new \Dullahan\Event\Entity\PreUpdate($entity, $payload);
        $this->eventDispatcher->dispatch($pre);
        $payload = $pre->getPayload();

        $this->fillEntity($class, $entity, $payload, $this->getEntityDefinition($entity));

        /*
         * Validating ownership twice.
         *
         * Entities are matched using changeable values like expansion or chapter, so we have to check ownership before
         * and after the change, so they won't attach their own entities to someone's else object
         * and change ownership by doing so.
         */
        if ($this->validateOwner && !$entity->isOwner($this->user ?? $this->userService->getLoggedInUser())) {
            throw new \Exception(
                'You cannot update chosen entity as you would be transferring ownership to different user',
                403
            );
        }

        if ($persist) {
            $this->em->persist($entity);
            $this->em->flush();
        }

        if ($entity instanceof InheritanceAwareInterface && $entity->getParent()) {
            $entity->getParent()->addChild($entity);
        }
        $this->emptyIndicatorService->setEmptyIndicators($entity, $payload);

        $this->eventDispatcher->dispatch(new \Dullahan\Event\Entity\PostUpdate($entity, $payload));
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
        if (!$entity instanceof ManageableInterface) {
            throw new \Exception('Chosen entity cannot be deleted', 400);
        }

        if ($this->validateOwner && !$entity->isOwner($this->user ?? $this->userService->getLoggedInUser())) {
            throw new \Exception("You cannot delete chosen entity as it doesn't belong to you", 403);
        }

        $this->eventDispatcher->dispatch(new \Dullahan\Event\Entity\PreRemove($entity));

        if ($entity instanceof InheritanceAwareInterface) {
            $id = $entity->getId();
            $this->removeParent($entity);
            $this->emptyIndicatorService->removeEmptyIndicators($entity::class, (int) $id);
        } else {
            $this->em->remove($entity);
            $this->em->flush();
        }

        $this->eventDispatcher->dispatch(new \Dullahan\Event\Entity\PostRemove($entity));
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
