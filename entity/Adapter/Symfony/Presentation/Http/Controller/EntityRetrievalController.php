<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Http\Controller;

use Dullahan\Entity\Port\Domain\EntityServiceInterface;
use Dullahan\Entity\Presentation\Http\Model\Parameter\BulkDTO;
use Dullahan\Entity\Presentation\Http\Model\Parameter\DataSetDTO;
use Dullahan\Entity\Presentation\Http\Model\Parameter\PaginationDTO;
use Dullahan\Entity\Presentation\Http\Response\BulkResponse;
use Dullahan\Main\Service\ProjectManagerService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Entity Managment')]
#[Route('/entity/', name: 'api_entity_retrieval_')]
class EntityRetrievalController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected EntityServiceInterface $entityUtilService,
        protected ProjectManagerService $projectManagerService,
    ) {
    }

    #[Route(
        '{project}/{namespace}',
        name: 'list',
        methods: 'GET',
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
    public function list(Request $request, string $project, string $namespace): JsonResponse
    {
        /** @var class-string $class */
        $class = $this->projectManagerService->urlSlugNamespaceToClassName(
            $project,
            $namespace,
        );
        $pagination = json_decode($request->get('pagination') ?? '[]', true);
        $dataSet = json_decode($request->get('dataSet') ?? '', true) ?: null;
        $inherit = json_decode($request->get('inherit') ?? '', true) ?: true;
        // @TODO make special interface
        $repo = $this->entityUtilService->getRepository($class);
        if (!method_exists($repo, 'list') || !method_exists($repo, 'total')) {
            throw new \Exception("This entity repository doesn't implement list retrieval", 400);
        }

        $total = $repo->total($pagination);
        $entities = $repo->list($pagination);
        $serialized = [];
        foreach ($entities as $entity) {
            $serialized[] = $this->entityUtilService->serialize($entity, $dataSet, $inherit);
        }

        return $this->httpUtilService->jsonResponse(
            'Entities retrieved successfully',
            data: [
                'entities' => $serialized,
            ],
            total: $total,
        );
    }

    #[Route(
        '{project}/bulk',
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
    public function bulk(Request $request, string $project): JsonResponse
    {
        $bulk = $request->get('bulk');
        if (!$bulk) {
            throw new \Exception('Bulk parameter is required to use bulk end point', 400);
        }
        $bulk = json_decode($bulk, true);
        if (!is_array($bulk)) {
            throw new \Exception('Bulk must be an array', 400);
        }

        $serializedBulk = [];
        foreach ($bulk as $name => $item) {
            if (!isset($item['namespace'])) {
                throw new \Exception(sprintf('Bulk item %s is missing namespace', $name), 400);
            }

            $namespace = $item['namespace'];
            $pagination = $item['pagination'] ?? [];
            $dataSet = $item['dataSet'] ?? null;
            $inherit = $item['inherit'] ?? true;

            /** @var class-string $class */
            $class = $this->projectManagerService->urlSlugNamespaceToClassName(
                $project,
                $namespace,
            );
            // @TODO make special interface
            $repo = $this->entityUtilService->getRepository($class);
            if (!method_exists($repo, 'list') || !method_exists($repo, 'total')) {
                throw new \Exception("This entity repository doesn't implement list retrieval", 400);
            }

            $entities = $repo->list($pagination);
            $serialized = [];
            foreach ($entities as $entity) {
                $serialized[] = $this->entityUtilService->serialize($entity, $dataSet, $inherit);
            }

            $serializedBulk[$name] = [
                'entities' => $serialized,
                'limit' => HttpUtilService::getLimit(),
                'offset' => HttpUtilService::getOffset(),
                'total' => $repo->total($pagination),
            ];

            HttpUtilService::setLimit(null);
            HttpUtilService::setOffset(null);
            HttpUtilService::setTotal(null);
        }

        return $this->httpUtilService->jsonResponse('Bulk entities retrieved successfully', data: [
            'bulk' => $serializedBulk,
        ]);
    }

    #[Route(
        '{project}/{namespace}/{id<\d+>}',
        name: 'view',
        methods: 'GET',
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
    public function view(Request $request, string $project, string $namespace, int $id): JsonResponse
    {
        /** @var class-string $class */
        $class = $this->projectManagerService->urlSlugNamespaceToClassName(
            $project,
            $namespace,
        );
        $dataSet = json_decode($request->get('dataSet') ?? '', true) ?: null;
        $inherit = json_decode($request->get('inherit') ?? '', true) ?: true;
        $entity = $this->entityUtilService->get($class, $id);

        $serialized = $this->entityUtilService->serialize($entity, $dataSet, $inherit);

        return $this->httpUtilService->jsonResponse('Entity retrieved successfully', data: [
            'entity' => $serialized,
        ]);
    }
}
