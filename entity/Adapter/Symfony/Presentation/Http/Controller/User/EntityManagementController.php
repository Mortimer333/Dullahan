<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Http\Controller\User;

use Dullahan\Entity\Port\Domain\EntityServiceInterface;
use Dullahan\Entity\Port\Domain\MappingsManagerInterface;
use Dullahan\Entity\Presentation\Event\Transport\Saga\CreateEntitySaga;
use Dullahan\Entity\Presentation\Event\Transport\Saga\ViewEntitySaga;
use Dullahan\Entity\Presentation\Http\Model\Body\CreateUpdateBody;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Exception\SagaNotHandledException;
use Dullahan\Main\Model\Response\Response;
use Dullahan\Main\Service\RequestFactory;
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
        protected EventDispatcherInterface $eventDispatcher,
        protected RequestFactory $requestFactory,
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
        $dullahanRequest = $this->requestFactory->symfonyToDullahanRequest($request);
        /** @var Response|null $response */
        $response = $this->eventDispatcher->dispatch(new CreateEntitySaga(
            $mapping,
            $path,
            $body['entity'] ?? [],
            $dullahanRequest,
        ))->getResponse();
        if (!$response) {
            throw new SagaNotHandledException('Create entity was not handled');
        }

        if (!$response->success) {
            throw new \Exception($response->message);
        }

        $id = $response->data['id'] ?? null;
        if (!$id) {
            throw new \Exception('Entity creation failed, missing ID', 500);
        }

        $response = $this->eventDispatcher->dispatch(new ViewEntitySaga(
            $mapping,
            $path,
            $id,
            $dullahanRequest,
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
