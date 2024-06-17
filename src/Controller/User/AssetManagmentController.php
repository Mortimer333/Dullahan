<?php

declare(strict_types=1);

namespace Dullahan\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Dullahan\Contract\Marker\UserServiceInterface;
use Dullahan\Entity\Asset;
use Dullahan\Model\Parameter\PaginationDTO;
use Dullahan\Model\Response\PAM\RetrieveImageResponse;
use Dullahan\Model\Response\PAM\RetrieveImagesResponse;
use Dullahan\Model\Response\PAM\UploadImageResponse;
use Dullahan\Service\AssetService;
use Dullahan\Service\Util\HttpUtilService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Asset Management')]
#[Route('/asset', name: 'api_asset_managment_')]
class AssetManagmentController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected AssetService $assetService,
        protected UserServiceInterface $userService,
        protected EntityManagerInterface $em,
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
            'image' => $this->assetService->serialize($this->assetService->get($id)),
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
            $images[] = $this->assetService->serialize($imageEntity);
        }

        return $this->httpUtilService->jsonResponse('Images retrieved successfully', data: [
            'images' => $images,
        ]);
    }

    #[Route(
        '/upload/{project}/image',
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
    public function uploadImage(Request $request, string $project): JsonResponse
    {
        $name = $request->request->get('name');
        $path = (string) ($request->request->get('path') ?? '');
        if ('null' == $path) {
            $path = '';
        }
        if (!$name) {
            try {
                $body = $this->httpUtilService->getBody($request);
                $name = $body['name'] ?? null;
            } catch (\Exception) {
                $name = null;
            }
        }
        if ('null' === $name) {
            $name = null;
        }

        /** @var ?UploadedFile $image */
        $image = $request->files->get('image');
        if (!$image) {
            throw new \Exception('Sent image not found', 404);
        }
        $user = $this->userService->getLoggedInUser();
        $image = $this->assetService->uploadImageToFE(
            $project,
            'user/' . $user->getData()?->getPublicId() . '/' . $path,
            $image,
            $name,
        );
        $this->em->persist($image);
        $this->em->flush();

        return $this->httpUtilService->jsonResponse('Image uploaded successfully', data: [
            'image' => $this->assetService->serialize($image),
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

        /** @var ?UploadedFile $image */
        $image = $request->files->get('image');
        if (!$image) {
            throw new \Exception('Sent image not found', 404);
        }

        $user = $this->userService->getLoggedInUser();
        if ($user->getId() != $asset->getUser()?->getId()) {
            throw new \Exception('Unauthorized access', 401);
        }

        $image = $this->assetService->updateImage($image, $asset);
        $this->em->persist($image);
        $this->em->flush();

        return $this->httpUtilService->jsonResponse('Image updated successfully', data: [
            'image' => $this->assetService->serialize($image),
        ]);
    }

    #[Route(
        '/{id<\d+>}/remove',
        name: 'remove',
        methods: 'DELETE',
    )]
    public function remove(int $id): JsonResponse
    {
        $this->assetService->remove($id);

        return $this->httpUtilService->jsonResponse('Asset successfully deleted');
    }
}
