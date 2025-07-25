<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction\Saga;

use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\BulkListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ListEntitiesSaga;
use Dullahan\Main\Model\Response\Response;
use Dullahan\Main\Service\Util\HttpUtilService;

class BulkListEntitiesSagaFunctor
{
    public function __construct(
        protected MappingsManagerInterface $projectManagerService,
        protected ListEntitiesSagaFunctor $listFunctor,
    ) {
    }

    public function __invoke(BulkListEntitiesSaga $event): Response
    {
        $mapping = $event->mapping;
        $request = $event->request;

        $bulk = $request->getQueryParameter('bulk');
        if (!$bulk) {
            throw new \Exception('Bulk parameter is required to use bulk end point', 400);
        }
        $bulk = json_decode($bulk, true);
        if (!is_array($bulk)) {
            throw new \Exception('Bulk must be an array', 400);
        }

        $serializedBulk = [];
        foreach ($bulk as $name => $item) {
            if (!isset($item['path'])) {
                throw new \Exception(sprintf('Bulk item %s is missing path', $name), 400);
            }

            /**
             * @TODO
             *      Bulk actions should be a separate process, probably asynchronous, it is unwise
             *      to run multiple actions in a single process - they were not designed to work together!
             */
            $listRequest = clone $request;
            $listRequest->setQueryParameter('pagination', $item['pagination'] ?? [])
                ->setQueryParameter('dataSet', $item['dataSet'] ?? [])
                ->setQueryParameter('inherit', $item['inherit'] ?? [])
            ;

            // @TODO Saga shouldn't start another story!
            $listResponse = ($this->listFunctor)(
                new ListEntitiesSaga(
                    $mapping,
                    $item['path'],
                    $listRequest,
                ),
            );

            $serializedBulk[$name] = [
                'entities' => $listResponse->data['entities'],
                'limit' => $listResponse->limit,
                'offset' => $listResponse->offset,
                'total' => $listResponse->total,
            ];

            HttpUtilService::setLimit(null);
            HttpUtilService::setOffset(null);
            HttpUtilService::setTotal(null);
        }

        return new Response(
            'Bulk entities retrieved successfully',
            data: [
                'bulk' => $serializedBulk,
            ],
        );
    }
}
