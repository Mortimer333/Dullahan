<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event\Process;

use Dullahan\Entity\Domain\DefaultAction\Process\BulkListEntitiesSagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\ListEntitiesSagaFunctor;
use Dullahan\Entity\Domain\DefaultAction\Process\ViewEntitySagaFunctor;
use Dullahan\Entity\Presentation\Event\Transport\Saga\BulkListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ViewEntitySaga;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityRetrievalProcessListener
{
    public function __construct(
        protected ViewEntitySagaFunctor $viewEntityProcessFunctor,
        protected ListEntitiesSagaFunctor $listEntitiesProcessFunctor,
        protected BulkListEntitiesSagaFunctor $bulkListEntitiesProcessFunctor,
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
}
