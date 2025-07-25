<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Saga;

use Dullahan\Entity\Port\Application\EntityPersistManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\CreateEntitySaga;
use Dullahan\Main\Model\Response\Response;

class CreateEntitySagaFunctor
{
    public function __construct(
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected MappingsManagerInterface $mappingsManager,
        protected EntityPersistManagerInterface $entityPersistManager,
    ) {
    }

    public function __invoke(CreateEntitySaga $event): Response
    {
        $class = $this->mappingsManager->mappingToClassName($event->mapping, $event->path);
        $entity = $this->entityPersistManager->create($class, $event->payload);

        return new Response(
            'Entity created successfully',
            data: [
                'id' => $entity->getId(),
            ]
        );
    }
}
