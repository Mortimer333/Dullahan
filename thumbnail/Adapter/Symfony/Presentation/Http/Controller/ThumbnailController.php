<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Symfony\Presentation\Http\Controller;

use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetServerInterface;
use Dullahan\Thumbnail\Adapter\Symfony\Presentation\UrlResolver\JackrabbitThumbnailUrlResolver;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailServiceInterface;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Asset Management')]
#[Route('/asset/thumbnail')]
class ThumbnailController extends AbstractController
{
    public function __construct(
        protected AssetPersistenceManagerInterface $assetManager,
        protected AssetServerInterface $assetServer,
        protected ThumbnailServiceInterface $thumbnailService,
    ) {
    }

    #[Route(
        '/{id<\d+>}/jackrabbit',
        name: JackrabbitThumbnailUrlResolver::RETRIEVE_PATH_NAME,
        methods: 'GET',
    )]
    public function getThumbnail(int $id): void
    {
        $this->assetServer->serve($this->thumbnailService->get($id)->structure);
    }
}
