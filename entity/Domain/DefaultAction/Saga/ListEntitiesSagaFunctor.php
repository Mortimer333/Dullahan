<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Saga;

use Dullahan\Entity\Domain\Service\RequestParametersHandler;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Application\EntitySerializerInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ListEntitiesSaga;
use Dullahan\Main\Model\Response\Response;
use Dullahan\Main\Service\Util\HttpUtilService;

class ListEntitiesSagaFunctor
{
    public function __construct(
        protected EntitySerializerInterface $entitySerializer,
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected MappingsManagerInterface $projectManagerService,
        protected RequestParametersHandler $requestParametersHandler,
    ) {
    }

    public function __invoke(ListEntitiesSaga $event): Response
    {
        $request = $event->request;
        $class = $this->projectManagerService->mappingToClassName($event->mapping, $event->path);
        $repo = $this->entityRetrievalManager->getRepository($class);
        // @TODO make this an interface
        // @TODO make exception
        if (!$repo || !method_exists($repo, 'list') || !method_exists($repo, 'total')) {
            throw new \Exception("This entity repository doesn't implement list retrieval", 422);
        }

        $pagination = $this->requestParametersHandler->retrievePagination($request);
        $entities = $repo->list($pagination);
        $serialized = [];
        foreach ($entities as $entity) {
            $serialized[] = $this->entitySerializer->serialize(
                $entity,
                $this->requestParametersHandler->retrieveDataSet($request),
                $this->requestParametersHandler->retrieveInherit($request),
            );
        }

        return new Response(
            'Entities retrieved successfully',
            data: ['entities' => $serialized],
            limit: HttpUtilService::getLimit(),
            offset: HttpUtilService::getOffset(),
            total: $repo->total($pagination),
        );
    }
}
