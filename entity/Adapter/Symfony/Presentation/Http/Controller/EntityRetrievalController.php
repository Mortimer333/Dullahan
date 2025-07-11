<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Http\Controller;

use Dullahan\Entity\Presentation\Event\Transport\Saga\BulkListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ListEntitiesSaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ViewEntitySaga;
use Dullahan\Entity\Presentation\Http\Model\Parameter\BulkDTO;
use Dullahan\Entity\Presentation\Http\Model\Parameter\DataSetDTO;
use Dullahan\Entity\Presentation\Http\Model\Parameter\PaginationDTO;
use Dullahan\Entity\Presentation\Http\Response\BulkResponse;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Exception\SagaNotHandledException;
use Dullahan\Main\Service\RequestFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Entity Management')]
#[Route('/entity/', name: 'api_entity_retrieval_')]
class EntityRetrievalController extends AbstractController
{
    public function __construct(
        protected RequestFactory $requestFactory,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(
        'list/{mapping}/{path}',
        name: 'list',
        methods: 'GET',
        requirements: ['path' => '.+'],
    )]
    #[SWG\Parameter(
        name: 'pagination',
        in: 'query',
        content: new SWG\JsonContent(ref: new Model(type: PaginationDTO::class))
    )]
    #[SWG\Parameter(
        name: 'dataSet',
        in: 'query',
        content: new SWG\JsonContent(ref: new Model(type: DataSetDTO::class))
    )]
    #[SWG\Parameter(
        name: 'inherit',
        in: 'query',
        schema: new SWG\Schema(type: 'boolean'),
        example: true
    )]
    public function list(Request $request, string $mapping, string $path): JsonResponse
    {
        $response = $this->eventDispatcher->dispatch(new ListEntitiesSaga(
            $mapping,
            $path,
            $this->requestFactory->symfonyToDullahanRequest($request),
        ))->getResponse();
        if (!$response) {
            throw new SagaNotHandledException('List process was not handled');
        }

        return new JsonResponse(
            $response->toArray(),
            $response->status,
            $response->headers,
        );
    }

    #[Route(
        'bulk/{mapping}',
        name: 'bulk',
        methods: 'GET',
        priority: 1
    )]
    #[SWG\Parameter(
        name: 'bulk',
        in: 'query',
        content: new SWG\JsonContent(type: 'object', properties: [
            new SWG\Property(property: 'name', ref: new Model(type: BulkDTO::class)),
        ])
    )]
    #[SWG\Response(
        description: 'Retrieved entities in bulk',
        content: new Model(type: BulkResponse::class),
        response: 200
    )]
    public function bulk(Request $request, string $mapping): JsonResponse
    {
        $response = $this->eventDispatcher->dispatch(new BulkListEntitiesSaga(
            $mapping,
            $this->requestFactory->symfonyToDullahanRequest($request),
        ))->getResponse();
        if (!$response) {
            throw new SagaNotHandledException('Bulk list process was not handled');
        }

        return new JsonResponse(
            $response->toArray(),
            $response->status,
            $response->headers,
        );
    }

    #[Route(
        'get/{mapping}/{path}/{id<\d+>}',
        name: 'view',
        methods: 'GET',
        requirements: ['path' => '.+'],
    )]
    #[SWG\Parameter(
        name: 'dataSet',
        in: 'query',
        content: new SWG\JsonContent(ref: new Model(type: DataSetDTO::class))
    )]
    #[SWG\Parameter(
        name: 'inherit',
        in: 'query',
        schema: new SWG\Schema(type: 'boolean'),
        example: true
    )]
    public function view(Request $request, string $mapping, string $path, int $id): JsonResponse
    {
        $response = $this->eventDispatcher->dispatch(new ViewEntitySaga(
            $mapping,
            $path,
            $id,
            $this->requestFactory->symfonyToDullahanRequest($request),
        ))->getResponse();
        if (!$response) {
            throw new SagaNotHandledException('View process was not handled');
        }

        return new JsonResponse(
            $response->toArray(),
            $response->status,
            $response->headers,
        );
    }
}
