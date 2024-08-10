<?php

declare(strict_types=1);

namespace Dullahan\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Dullahan\Asset\UploadedFile;
use Dullahan\Contract\AssetManager\AssetManagerInterface;
use Dullahan\Contract\AssetManager\AssetSerializerInterface;
use Dullahan\Contract\Marker\UserServiceInterface;
use Dullahan\Entity\Asset;
use Dullahan\Model\Parameter\PaginationDTO;
use Dullahan\Model\Response\PAM\RetrieveImageResponse;
use Dullahan\Model\Response\PAM\RetrieveImagesResponse;
use Dullahan\Model\Response\PAM\UploadImageResponse;
use Dullahan\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File;
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
        protected HttpUtilService        $httpUtilService,
        protected AssetManagerInterface  $assetManager,
        protected UserServiceInterface   $userService,
        protected EntityManagerInterface $em,
        protected AssetSerializerInterface $assetSerializer,
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
            'image' => $this->assetSerializer->serialize($this->assetManager->get($id)),
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
        $pagination = json_decode($request->get('pagination') ?? '[]', true);
        $images = [];
        $user = $this->userService->getLoggedInUser();
        $imageEntities = $this->em->getRepository(Asset::class)->list(
            $pagination,
            function (QueryBuilder $qb) use ($user) {
                $qb->andWhere('p.userData = :userData')
                    ->setParameter('userData', $user->getData())
                ;
            }
        );
        foreach ($imageEntities as $imageEntity) {
            $images[] = $this->assetSerializer->serialize($imageEntity);
        }

        return $this->httpUtilService->jsonResponse('Images retrieved successfully', data: [
            'images' => $images,
        ]);
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
        if (!($image instanceof File\UploadedFile)) {
            return $this->httpUtilService->jsonResponse(
                'Sent image was not found',
                Response::HTTP_NOT_FOUND,
                false,
            );
        }

        $image = $this->assetManager->upload(
            $path,
            $name,
            new UploadedFile($image),
        );
        $this->assetManager->flush();

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
        $asset = $this->assetManager->get($id);

        $image = $request->files->get('image');
        if (!($image instanceof File\UploadedFile)) {
            throw new \Exception('Sent image not found', 404);
        }

        // @TODO potential place for another ownership event
        $user = $this->userService->getLoggedInUser();
        if ($user->getId() != $asset->getOwner()?->getId()) {
            throw new \Exception('Unauthorized access', 401);
        }

        $asset = $this->assetManager->move($asset, $asset->getPath(), new UploadedFile($image));
        $this->assetManager->flush();

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
        $this->assetManager->remove($this->assetManager->get($id));
        $this->assetManager->flush();

        return $this->httpUtilService->jsonResponse('Asset successfully deleted');
    }
}
