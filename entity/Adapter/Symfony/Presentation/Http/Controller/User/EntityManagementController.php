<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Http\Controller\User;

use Dullahan\Entity\Port\Domain\EntityServiceInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Http\Model\Body\CreateUpdateBody;
use Dullahan\Main\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Entity Management')]
#[Route('/entity/', name: 'api_entity_management_')]
class EntityManagementController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected EntityServiceInterface $entityUtilService,
        protected MappingsManagerInterface $projectManagerService,
    ) {
    }

    #[Route(
        'create/{mapping}/{path}',
        name: 'create',
        methods: 'POST',
        requirements: ['path' => '.+'],
    )]
    #[SWG\RequestBody(attachables: [new Model(type: CreateUpdateBody::class)])]
    public function create(Request $request, string $mapping, string $path): JsonResponse
    {
        $body = $this->httpUtilService->getBody($request);
        $dataSet = $body['dataSet'] ?? null;
        $class = $this->projectManagerService->mappingToClassName($mapping, $path);
        $entity = $this->entityUtilService->create($class, $body['entity'] ?? []);

        return $this->httpUtilService->jsonResponse('Entity successfully created', data: [
            'entity' => $this->entityUtilService->serialize($entity, $dataSet),
        ]);
    }

    #[Route(
        'update/{mapping}/{path}/{id<\d+>}',
        name: 'update',
        methods: 'PUT',
        requirements: ['path' => '.+'],
    )]
    #[SWG\RequestBody(attachables: [new Model(type: CreateUpdateBody::class)])]
    public function update(Request $request, string $mapping, string $path, int $id): JsonResponse
    {
        $body = $this->httpUtilService->getBody($request);
        $dataSet = $body['dataSet'] ?? null;
        $class = $this->projectManagerService->mappingToClassName($mapping, $path);
        $entity = $this->entityUtilService->update($class, $id, $body['entity'] ?? []);

        return $this->httpUtilService->jsonResponse('Entity successfully updated', data: [
            'entity' => $this->entityUtilService->serialize($entity, $dataSet),
        ]);
    }

    #[Route(
        'delete/{mapping}/{path}/{id<\d+>}',
        name: 'remove',
        methods: 'DELETE',
        requirements: ['path' => '.+'],
    )]
    public function remove(string $mapping, string $path, int $id): JsonResponse
    {
        $class = $this->projectManagerService->mappingToClassName($mapping, $path);
        $this->entityUtilService->remove($class, $id);

        return $this->httpUtilService->jsonResponse('Entity successfully deleted');
    }
}
