<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Http\Controller;

use Dullahan\Asset\Adapter\Symfony\Application\UrlResolver\JackrabbitUrlResolver;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetMiddlewareInterface;
use Dullahan\Asset\Port\Presentation\AssetServerInterface;
use Dullahan\Asset\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Presentation\HTTP\Response\PAM\RetrieveImageResponse;
use Dullahan\Asset\Presentation\HTTP\Response\PAM\RetrieveImagesResponse;
use Dullahan\Main\Model\Parameter\PaginationDTO;
use Dullahan\Main\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Asset Management')]
#[Route('/asset')]
class AssetManagmentController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected AssetPersistenceManagerInterface $assetManager,
        protected AssetServerInterface $assetServer,
        protected AssetServiceInterface $assetService,
        protected AssetMiddlewareInterface $assetMiddleware,
    ) {
    }

    #[Route('/{id<\d+>}/jackrabbit', name: JackrabbitUrlResolver::IMAGE_PATH_NAME, methods: 'GET')]
    public function serveJackrabbit(int $id): Response
    {
        $this->assetServer->serve($this->assetService->get($id)->structure);

        return new Response('');
    }

    #[Route('/{id<\d+>}', name: 'api_asset_management_get', methods: 'GET')]
    #[SWG\Response(
        description: 'Get image',
        content: new Model(type: RetrieveImageResponse::class),
        response: 200
    )]
    public function get(int $id): JsonResponse
    {
        return $this->httpUtilService->jsonResponse('Image retrieved successfully', data: [
            'image' => $this->assetMiddleware->retrieve($id),
        ]);
    }

    #[Route('/list', name: 'api_asset_management_list', methods: 'GET')]
    #[SWG\Parameter(
        name: 'pagination',
        in: 'query',
        content: new SWG\JsonContent(ref: new Model(type: PaginationDTO::class))
    )]
    #[SWG\Response(
        description: 'List of images',
        content: new Model(type: RetrieveImagesResponse::class),
        response: 200
    )]
    public function list(Request $request): JsonResponse
    {
        $pagination = $request->get('pagination') ?? [];
        if (is_string($pagination)) {
            $pagination = json_decode($pagination, true);
        }

        return $this->httpUtilService->jsonResponse('Images retrieved successfully', data: [
            'images' => $this->assetMiddleware->list($pagination),
        ]);
    }
}
