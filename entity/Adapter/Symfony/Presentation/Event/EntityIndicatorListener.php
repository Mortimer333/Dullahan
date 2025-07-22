<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Domain\Trait\IndicatorMethodsTrait;
use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Entity\Port\Domain\IndicatorAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\CreateEntity;
use Dullahan\Entity\Presentation\Event\Transport\PersistCreatedEntity;
use Dullahan\Entity\Presentation\Event\Transport\PersistUpdatedEntity;
use Dullahan\Entity\Presentation\Event\Transport\RemoveEntity;
use Dullahan\Entity\Presentation\Event\Transport\UpdateEntity;
use Dullahan\Main\Model\EventAbstract;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityIndicatorListener
{
    use IndicatorMethodsTrait;

    public function __construct(
        protected EntityCacheServiceInterface $entityCacheService,
        protected EntityManagerInterface $em,
    ) {
    }

    /** @var array<array{0: IndicatorAwareInterface, 1: object, 2: int}> */
    protected array $spacePopers = [];
    /** @var array<array{0: IndicatorAwareInterface}> */
    protected array $spaceMakers = [];
    /** @var array<array{0: IndicatorAwareInterface}> */
    protected array $spaceMovers = [];

    #[AsEventListener(event: RemoveEntity::class, priority: EventAbstract::PRIORITY_FIRST)]
    public function onIndicatorAwareRemoval(RemoveEntity $event): void
    {
        $entity = $event->entity;
        if (
            !$entity instanceof IndicatorAwareInterface
            || is_null($entity->getIndicator())
            || is_null($entity->getParent())
        ) {
            return;
        }

        $this->spacePopers[] = [$entity, $entity->getParent(), $entity->getIndicator()];
    }

    #[AsEventListener(event: CreateEntity::class, priority: -10)]
    public function preIndicatorAwareCreation(CreateEntity $event): void
    {
        $entity = $event->entity;
        if (!($entity instanceof IndicatorAwareInterface)) {
            return;
        }

        $payload = $event->payload;
        if (!array_key_exists($entity->getParentField(), $payload)) {
            return;
        }

        if (!isset($payload['indicator'])) {
            $entity->setIndicator(
                $this->getLastIndicator(
                    $entity::class,
                    $entity->getParentField(),
                    $payload[$entity->getParentField()],
                ) + 1,
            );
        }

        $this->spaceMakers[] = [$entity];
    }

    #[AsEventListener(event: UpdateEntity::class)]
    public function preIndicatorAwareUpdate(UpdateEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof IndicatorAwareInterface) {
            return;
        }

        $payload = $event->payload;
        if (!array_key_exists($entity->getParentField(), $payload)) {
            if (!isset($payload['indicator'])) {
                return;
            }

            $this->spaceMovers[] = [$entity, $entity->getIndicator()];

            return;
        }

        $this->handleIndicatorParentChange($payload, $entity);
    }

    /**
     * All maintenance tasks are moved after the creation/update, so we don't change order if the change haven't been
     * persisted in the DB.
     */
    #[AsEventListener(event: PersistUpdatedEntity::class, priority: EventAbstract::PRIORITY_LAST)]
    #[AsEventListener(event: PersistCreatedEntity::class, priority: EventAbstract::PRIORITY_LAST)]
    #[AsEventListener(event: RemoveEntity::class, priority: EventAbstract::PRIORITY_LAST)]
    public function postIndicatorAwareCreation(PersistUpdatedEntity|PersistCreatedEntity|RemoveEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity instanceof IndicatorAwareInterface) {
            return;
        }

        $changed = [];

        /** @var array{0: IndicatorAwareInterface, 1: object, 2: int} $spacePoperInfo */
        foreach ($this->spacePopers as $spacePoperInfo) {
            [$entity, $parent, $indicator] = $spacePoperInfo;
            $changed = array_merge($changed, $this->popSpace($entity, $indicator, $parent));
        }
        $this->spacePopers = [];

        /** @var array{0: IndicatorAwareInterface} $spaceMakerInfo */
        foreach ($this->spaceMakers as $spaceMakerInfo) {
            [$entity] = $spaceMakerInfo;
            if (is_null($entity->getIndicator())) {
                continue;
            }
            $changed = array_merge($changed, $this->makeSpace($entity, $entity->getIndicator()));
        }
        $this->spaceMakers = [];

        /** @var array{0: IndicatorAwareInterface, 1: int} $spaceMoveInfo */
        foreach ($this->spaceMovers as $spaceMoveInfo) {
            [$entity, $old] = $spaceMoveInfo;
            if (is_null($entity->getIndicator())) {
                continue;
            }
            $changed = array_merge(
                $changed,
                $this->moveIndicator($entity, $entity->getIndicator(), oldIndicator: $old)
            );
        }
        $this->spaceMovers = [];

        foreach ($changed as $id) {
            $this->entityCacheService->deleteCacheById($id, $entity::class);
        }
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param array<int|string, mixed> $payload
     */
    protected function handleIndicatorParentChange(array $payload, IndicatorAwareInterface $entity): void
    {
        $newParent = $payload[$entity->getParentField()] ?? null;
        $oldParent = $entity->getParent();
        $parentChanged = $oldParent && method_exists($oldParent, 'getId') && $newParent !== $oldParent->getId();

        // If entity is removing or replacing its parent remove old indicator
        if (
            $entity->getParent()
            && (
                is_null($newParent)
                || $parentChanged
            )
            && !is_null($entity->getIndicator())
        ) {
            $this->spacePopers[] = [$entity, $entity->getParent(), $entity->getIndicator()];

            return;
        }

        if ($parentChanged) {
            $this->spaceMakers[] = [$entity];

            return;
        }
    }
}
