<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Presentation\Http\Controller\User;

use Dullahan\Main\Service\ProjectManagerService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Object\Port\Domain\EntityServiceInterface;
use Dullahan\Object\Presentation\Http\Model\Body\CreateUpdateBody;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Entity Managment')]
#[Route('/entity/', name: 'api_entity_management_')]
class EntityManagmentController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected EntityServiceInterface $entityUtilService,
        protected ProjectManagerService $projectManagerService,
    ) {
    }

    #[Route(
        '{project}/{namespace}',
        name: 'create',
        methods: 'POST',
    )]
    #[SWG\RequestBody(attachables: [new Model(type: CreateUpdateBody::class)])]
    public function create(Request $request, string $project, string $namespace): JsonResponse
    {
        $body = $this->httpUtilService->getBody($request);
        $dataSet = $body['dataSet'] ?? null;
        /** @var class-string $class */
        $class = $this->projectManagerService->urlSlugNamespaceToClassName(
            $project,
            $namespace,
        );
        $entity = $this->entityUtilService->create($class, $body['entity'] ?? []);

        return $this->httpUtilService->jsonResponse('Entity successfully created', data: [
            'entity' => $this->entityUtilService->serialize($entity, $dataSet),
        ]);
    }

    #[Route(
        '{project}/{namespace}/{id<\d+>}',
        name: 'update',
        methods: 'PUT',
    )]
    #[SWG\RequestBody(attachables: [new Model(type: CreateUpdateBody::class)])]
    public function update(Request $request, string $project, string $namespace, int $id): JsonResponse
    {
        $body = $this->httpUtilService->getBody($request);
        $dataSet = $body['dataSet'] ?? null;
        /** @var class-string $class */
        $class = $this->projectManagerService->urlSlugNamespaceToClassName(
            $project,
            $namespace,
        );
        $entity = $this->entityUtilService->update($class, $id, $body['entity'] ?? []);

        return $this->httpUtilService->jsonResponse('Entity successfully updated', data: [
            'entity' => $this->entityUtilService->serialize($entity, $dataSet),
        ]);
    }

    #[Route(
        '{project}/{namespace}/{id<\d+>}',
        name: 'remove',
        methods: 'DELETE',
    )]
    public function remove(string $project, string $namespace, int $id): JsonResponse
    {
        /** @var class-string $class */
        $class = $this->projectManagerService->urlSlugNamespaceToClassName(
            $project,
            $namespace,
        );
        $this->entityUtilService->remove($class, $id);

        return $this->httpUtilService->jsonResponse('Entity successfully deleted');
    }
}
