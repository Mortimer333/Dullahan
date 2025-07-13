<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\Process\BulkListEntitiesSagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\CreateEntitySagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\ListEntitiesSagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\RemoveEntitySagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\UpdateEntitySagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\ViewEntitySagaFunctor;
use Dullahan\Entity\Presentation\Event\Transport\Saga\BulkListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\CreateEntitySaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\RemoveEntitySaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\UpdateEntitySaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ViewEntitySaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntitySagaListener
{
    public function __construct(
        protected ViewEntitySagaFunctor $viewEntityProcessFunctor,
        protected ListEntitiesSagaFunctor $listEntitiesProcessFunctor,
        protected BulkListEntitiesSagaFunctor $bulkListEntitiesProcessFunctor,
        protected CreateEntitySagaFunctor $createEntitySagaFunctor,
        protected UpdateEntitySagaFunctor $updateEntitySagaFunctor,
        protected RemoveEntitySagaFunctor $removeEntitySagaFunctor,
    ) {
    }

    #[AsEventListener(event: ViewEntitySaga::class)]
    public function onViewEntitySaga(ViewEntitySaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setResponse(($this->viewEntityProcessFunctor)($event));
    }

    #[AsEventListener(event: ListEntitiesSaga::class)]
    public function onListEntitiesSaga(ListEntitiesSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setResponse(($this->listEntitiesProcessFunctor)($event));
    }

    #[AsEventListener(event: BulkListEntitiesSaga::class)]
    public function onBulkListEntitiesSaga(BulkListEntitiesSaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setResponse(($this->bulkListEntitiesProcessFunctor)($event));
    }

    #[AsEventListener(event: CreateEntitySaga::class)]
    public function onCreateEntitySaga(CreateEntitySaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setResponse(($this->createEntitySagaFunctor)($event));
    }

    #[AsEventListener(event: UpdateEntitySaga::class)]
    public function onUpdateEntitySaga(UpdateEntitySaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setResponse(($this->updateEntitySagaFunctor)($event));
    }

    #[AsEventListener(event: RemoveEntitySaga::class)]
    public function onRemoveEntitySaga(RemoveEntitySaga $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setResponse(($this->removeEntitySagaFunctor)($event));
    }
}
