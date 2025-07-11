<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Process;

use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\EntityServiceInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ListEntitiesSaga;
use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\Response\Response;
use Dullahan\Main\Service\Util\HttpUtilService;

class ListEntitiesSagaFunctor
{
    public function __construct(
        protected EntityServiceInterface $entityUtilService,
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected MappingsManagerInterface $projectManagerService,
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

        $pagination = $this->retrievePagination($request);
        $entities = $repo->list($pagination);
        $serialized = [];
        foreach ($entities as $entity) {
            $serialized[] = $this->entityUtilService->serialize(
                $entity,
                $this->retrieveDataSet($request),
                (bool) $request->get('inherit', true),
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

    protected function retrieveDataSet(RequestInterface $request): mixed
    {
        $dataSet = $request->get('dataSet');
        if (is_string($dataSet)) {
            $dataSet = json_decode($dataSet, true) ?: null;
        }

        if (!is_null($dataSet) && !is_array($dataSet)) {
            throw new \InvalidArgumentException('Data Set is invalid', 400);
        }

        return $dataSet;
    }

    protected function retrievePagination(RequestInterface $request): mixed
    {
        $pagination = $request->get('pagination', '[]');
        if (is_string($pagination)) {
            $pagination = json_decode($pagination, true) ?: [];
        }

        if (!is_null($pagination) && !is_array($pagination)) {
            throw new \InvalidArgumentException('Pagination is invalid', 400);
        }

        return $pagination;
    }
}
