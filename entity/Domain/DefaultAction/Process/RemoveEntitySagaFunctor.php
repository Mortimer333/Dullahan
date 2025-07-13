<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Process;

use Dullahan\Entity\Port\Application\EntityPersistManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\RemoveEntitySaga;
use Dullahan\Main\Model\Response\Response;

class RemoveEntitySagaFunctor
{
    public function __construct(
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected MappingsManagerInterface $mappingsManager,
        protected EntityPersistManagerInterface $entityPersistManager,
    ) {
    }

    public function __invoke(RemoveEntitySaga $event): Response
    {
        $class = $this->mappingsManager->mappingToClassName($event->mapping, $event->path);
        $this->entityPersistManager->remove($class, $event->id);

        return new Response('Entity removed successfully');
    }
}
