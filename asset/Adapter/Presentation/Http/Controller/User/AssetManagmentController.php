<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Http\Controller\User;

use Dullahan\Asset\Adapter\Presentation\Http\Model\Body\CreateFolderDTO;
use Dullahan\Asset\Adapter\Presentation\Http\Model\Body\MoveDTO;
use Dullahan\Asset\Adapter\Presentation\Http\Model\Response\PAM\UploadImageResponse;
use Dullahan\Asset\Application\Port\Presentation\AssetMiddlewareInterface;
use Dullahan\Main\Attribute\RequestPayload;
use Dullahan\Main\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Asset Management')]
#[Route('/asset', name: 'api_asset_managment_')]
class AssetManagmentController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected AssetMiddlewareInterface $assetMiddleware,
    ) {
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
        // @TODO maybe move it somewhere? can't really find proper place that wouldn't make more issues then resolve
        //     maybe just create DTO object and validate with mapper attribute?
        $name = $request->request->get('name');
        $path = $request->request->get('path');
        if (empty($path) || !is_string($path)) {
            return $this->httpUtilService->jsonResponse(
                'Image lacks appropriate path',
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
                'Image lacks appropriate name',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
            );
        }

        $file = $request->files->get('image');
        if (!($file instanceof UploadedFile)) {
            return $this->httpUtilService->jsonResponse(
                'Sent image was not found',
                Response::HTTP_NOT_FOUND,
                false,
            );
        }

        return $this->httpUtilService->jsonResponse('Image uploaded successfully', data: [
            'image' => $this->assetMiddleware->upload(
                $name,
                $path,
                fopen($file->getRealPath(), 'r') ?: throw new \Exception('Couldn\'t read uploaded file', 422),
                $file->getClientOriginalName(),
                (int) $file->getSize(),
                (string) $file->guessExtension(),
                (string) $file->getMimeType(),
            ),
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
        $file = $request->files->get('image');
        if (!($file instanceof UploadedFile)) {
            throw new \Exception('Sent image not found', 404);
        }

        return $this->httpUtilService->jsonResponse('Image updated successfully', data: [
            'image' => $this->assetMiddleware->reupload(
                $id,
                fopen($file->getRealPath(), 'r') ?: throw new \Exception('Couldn\'t read uploaded file', 422),
                $file->getClientOriginalName(),
                (int) $file->getSize(),
                (string) $file->guessExtension(),
                (string) $file->getMimeType(),
            ),
        ]);
    }

    #[Route('/upload/folder', name: 'create_folder', methods: 'POST')]
    public function createFolder(#[RequestPayload] CreateFolderDTO $dto): JsonResponse
    {
        return $this->httpUtilService->jsonResponse('Folder created successfully', data: [
            'folder' => $this->assetMiddleware->folder($dto->parent ?? '', $dto->name ?? ''),
        ]);
    }

    #[Route('/move', name: 'move', methods: 'POST')]
    public function move(#[RequestPayload] MoveDTO $dto): JsonResponse
    {
        return $this->httpUtilService->jsonResponse('Asset moved successfully', data: [
            'asset' => $this->assetMiddleware->move($dto->from ?? '', $dto->to ?? ''),
        ]);
    }

    #[Route(
        '/{id<\d+>}/remove',
        name: 'remove',
        methods: 'DELETE',
    )]
    public function remove(int $id): JsonResponse
    {
        $this->assetMiddleware->remove($id);

        return $this->httpUtilService->jsonResponse('Asset successfully deleted');
    }
}
