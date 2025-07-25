<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Saga;

use Dullahan\Entity\Domain\Exception\EntityNotFoundException;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Application\EntitySerializerInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ViewEntitySaga;
use Dullahan\Main\Model\Response\Response;

class ViewEntitySagaFunctor
{
    public function __construct(
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected MappingsManagerInterface $mappingsManagerService,
        protected EntitySerializerInterface $entitySerializer,
    ) {
    }

    public function __invoke(ViewEntitySaga $event): Response
    {
        $request = $event->request;

        $class = $this->mappingsManagerService->mappingToClassName($event->mapping, $event->path);
        $entity = $this->entityRetrievalManager->get($class, $event->id);
        if (!$entity) {
            throw new EntityNotFoundException('Entity was not found');
        }

        return new Response(
            'Entity retrieved successfully',
            data: ['entity' => $this->entitySerializer->serialize(
                $entity,
                json_decode($request->getQueryParameter('dataSet') ?? '', true) ?: null,
                (bool) $request->getQueryParameter('inherit', true),
            )],
        );
    }
}
