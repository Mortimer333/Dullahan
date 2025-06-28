<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Domain\Trait\Entity;

use Dullahan\Entity\Domain\Trait\IndicatorMethodsTrait;
use Dullahan\Entity\Port\Domain\IndicatorAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\PostCreate;
use Dullahan\Entity\Presentation\Event\Transport\PostRemove;
use Dullahan\Entity\Presentation\Event\Transport\PostUpdate;
use Dullahan\Entity\Presentation\Event\Transport\PreCreate;
use Dullahan\Entity\Presentation\Event\Transport\PreRemove;
use Dullahan\Entity\Presentation\Event\Transport\PreUpdate;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

trait IndicatorTrait
{
    use IndicatorMethodsTrait;

    /** @var array<array{0: IndicatorAwareInterface, 1: object, 2: int}> */
    protected array $spacePopers = [];
    /** @var array<array{0: IndicatorAwareInterface}> */
    protected array $spaceMakers = [];
    /** @var array<array{0: IndicatorAwareInterface}> */
    protected array $spaceMovers = [];

    #[AsEventListener(event: PreRemove::class)]
    public function onIndicatorAwareRemoval(PreRemove $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof IndicatorAwareInterface || is_null($entity->getIndicator()) || !$entity->getParent()) {
            return;
        }

        $this->spacePopers[] = [$entity, $entity->getParent(), $entity->getIndicator()];
    }

    #[AsEventListener(event: PreCreate::class)]
    public function preIndicatorAwareCreation(PreCreate $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof IndicatorAwareInterface) {
            return;
        }

        $payload = $event->getPayload();
        if (
            !array_key_exists($entity->getParentField(), $payload)
            || !isset($payload['indicator'])
        ) {
            return;
        }

        $this->spaceMakers[] = [$entity];
    }

    #[AsEventListener(event: PreUpdate::class)]
    public function preIndicatorAwareUpdate(PreUpdate $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof IndicatorAwareInterface) {
            return;
        }

        $payload = $event->getPayload();
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
     * @param array<string, mixed> $payload
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

    /**
     * All maintenance tasks are moved after the creation/update, so we don't change order if the change haven't been
     * persisted in the DB.
     */
    #[AsEventListener(event: PostUpdate::class, priority: -256)]
    #[AsEventListener(event: PostCreate::class, priority: -256)]
    #[AsEventListener(event: PostRemove::class, priority: -256)]
    public function postIndicatorAwareCreation(PostUpdate|PostCreate|PostRemove $event): void
    {
        $entity = $event->getEntity();
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
            $this->entityUtilService->removeCacheById($id, $entity::class);
        }
    }
}
