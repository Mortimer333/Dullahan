<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Http\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Dullahan\Asset\Adapter\Presentation\Http\Model\Response\PAM\RetrieveImageResponse;
use Dullahan\Asset\Adapter\Presentation\Http\Model\Response\PAM\RetrieveImagesResponse;
use Dullahan\Asset\Adapter\Presentation\Http\Model\Response\PAM\UploadImageResponse;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetSerializerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Domain\File;
use Dullahan\Asset\Entity\Asset;
use Dullahan\Main\Contract\Marker\UserServiceInterface;
use Dullahan\Main\Model\Parameter\PaginationDTO;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// @TODO move all functionality to middleware as this is too much for controller
#[SWG\Tag('Project Asset Management')]
#[Route('/asset', name: 'api_asset_managment_')]
class AssetManagmentController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected AssetPersistenceManagerInterface $assetManager,
        protected UserServiceInterface $userService,
        protected EntityManagerInterface $em,
        protected AssetSerializerInterface $assetSerializer,
        protected AssetServiceInterface $assetService,
    ) {
    }

    #[Route('/{id<\d+>}', name: 'get', methods: 'GET')]
    #[SWG\Response(
        description: 'Get image',
        content: new Model(type: RetrieveImageResponse::class),
        response: 200
    )]
    public function get(int $id): JsonResponse
    {
        return $this->httpUtilService->jsonResponse('Image retrieved successfully', data: [
            'image' => $this->assetSerializer->serialize($this->assetService->get($id)),
        ]);
    }

    #[Route('/list', name: 'list', methods: 'GET')]
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
        // @TODO create new `list` method on AssetService
        $pagination = json_decode($request->get('pagination') ?? '[]', true);
        $images = [];
        $user = $this->userService->getLoggedInUser();
        $assets = $this->em->getRepository(Asset::class)->list(
            $pagination,
            function (QueryBuilder $qb) use ($user) {
                $qb->andWhere('p.userData = :userData')
                    ->setParameter('userData', $user->getData())
                ;
            }
        );
        foreach ($assets as $assetEntity) {
            $images[] = $this->assetSerializer->serialize($this->assetService->get($assetEntity->getId()));
        }

        return $this->httpUtilService->jsonResponse('Images retrieved successfully', data: ['images' => $images]);
    }

    #[Route(
        '/upload/image',
        name: 'upload',
        methods: 'POST',
    )]
    #[SWG\RequestBody(
        required: true,
        attachables: [new SWG\MediaType( // @phpstan-ignore-line
            mediaType: 'multipart/form-data',
            schema: new SWG\Schema(properties: [
                new SWG\Property(property: 'name'),
                new SWG\Property(property: 'path'),
                new SWG\Property(
                    description: 'file to upload',
                    property: 'image',
                    type: 'string',
                    format: 'binary',
                ),
            ])
        )],
    )]
    #[SWG\Response(
        description: 'Image uploaded',
        content: new Model(type: UploadImageResponse::class),
        response: 200
    )]
    public function uploadImage(Request $request): JsonResponse
    {
        $name = $request->request->get('name');
        $path = $request->request->get('path');
        BinUtilService::logToTest('path: ' . $path, 'a');
        BinUtilService::logToTest('name: ' . $name, 'a');
        if (empty($path) || !is_string($path)) {
            return $this->httpUtilService->jsonResponse(
                'Image is lacking appropriate path',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
            );
        }

        try {
            $name = $name ?: ($this->httpUtilService->getBody($request)['name'] ?? null);
        } catch (\Exception) {
            $name = null;
        }

        if (empty($name) || !is_string($name)) {
            return $this->httpUtilService->jsonResponse(
                'Image is lacking appropriate name',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
            );
        }

        $image = $request->files->get('image');
        if (!($image instanceof UploadedFile) || !($resource = fopen($image->getRealPath(), 'r'))) {
            return $this->httpUtilService->jsonResponse(
                'Sent image was not found',
                Response::HTTP_NOT_FOUND,
                false,
            );
        }

        $image = $this->assetService->create(
            new File(
                $path,
                $name,
                $image->getClientOriginalName(),
                $resource,
                (int) $image->getSize(),
                (string) $image->guessExtension(),
                (string) $image->getMimeType(),
            ));
        $this->assetService->flush();

        return $this->httpUtilService->jsonResponse('Image uploaded successfully', data: [
            'image' => $this->assetSerializer->serialize($image),
        ]);
    }

    #[Route('/upload/image/{id<\d+>}', name: 'update', methods: 'POST')]
    #[SWG\RequestBody(
        required: true,
        attachables: [new SWG\MediaType( // @phpstan-ignore-line
            mediaType: 'multipart/form-data',
            schema: new SWG\Schema(properties: [
                new SWG\Property(
                    description: 'file to upload',
                    property: 'image',
                    type: 'string',
                    format: 'binary',
                ),
            ])
        )],
    )]
    #[SWG\Response(
        description: 'Image updated',
        content: new Model(type: UploadImageResponse::class),
        response: 200
    )]
    public function updateImage(Request $request, int $id): JsonResponse
    {
        $asset = $this->assetService->get($id);

        $image = $request->files->get('image');
        if (!($image instanceof UploadedFile) || !($resource = fopen($image->getRealPath(), 'r'))) {
            throw new \Exception('Sent image not found', 404);
        }

        // @TODO potential place for another ownership event
        $user = $this->userService->getLoggedInUser();
        if ($user->getId() != $asset->entity->getOwner()?->getId()) {
            throw new \Exception('Unauthorized access', 401);
        }

        $asset = $this->assetService->replace($asset, new File(
            $asset->structure->path,
            $asset->structure->name,
            $image->getClientOriginalName(),
            $resource,
            (int) $image->getSize(),
            (string) $image->guessExtension(),
            (string) $image->getMimeType(),
        ));
        $this->assetService->flush();

        return $this->httpUtilService->jsonResponse('Image updated successfully', data: [
            'image' => $this->assetSerializer->serialize($asset),
        ]);
    }

    #[Route(
        '/{id<\d+>}/remove',
        name: 'remove',
        methods: 'DELETE',
    )]
    public function remove(int $id): JsonResponse
    {
        $this->assetService->remove($this->assetService->get($id));
        $this->assetService->flush();

        return $this->httpUtilService->jsonResponse('Asset successfully deleted');
    }
}
