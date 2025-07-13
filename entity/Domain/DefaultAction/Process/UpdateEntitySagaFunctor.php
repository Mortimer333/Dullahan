<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Process;

use Dullahan\Entity\Port\Application\EntityPersistManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\UpdateEntitySaga;
use Dullahan\Main\Model\Response\Response;

class UpdateEntitySagaFunctor
{
    public function __construct(
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected MappingsManagerInterface $mappingsManager,
        protected EntityPersistManagerInterface $entityPersistManager,
    ) {
    }

    public function __invoke(UpdateEntitySaga $event): Response
    {
        $class = $this->mappingsManager->mappingToClassName($event->mapping, $event->path);
        $entity = $this->entityPersistManager->update($class, $event->id, $event->payload);

        return new Response(
            'Entity updated successfully',
            data: [
                'id' => $entity->getId(),
            ]
        );
    }
}
