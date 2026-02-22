<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Symfony\Presentation\Http\Controller;

use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetServerInterface;
use Dullahan\Thumbnail\Adapter\Symfony\Presentation\UrlResolver\JackrabbitThumbnailUrlResolver;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailServiceInterface;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Asset Management - Thumbnail')]
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
        $thumbnail = $this->thumbnailService->get($id);
        /** @var \Dullahan\Thumbnail\Domain\Entity\Thumbnail $entity */
        $entity = $thumbnail->entity;
        $structure = new Structure(
            $thumbnail->structure->path,
            $thumbnail->structure->name,
            $thumbnail->structure->type,
            $thumbnail->structure->extension,
            $thumbnail->structure->mimeType ?: ($entity->getAsset()?->getMimeType() ?? ''),
            (int) ($thumbnail->structure->weight ?: $entity->getWeight()),
            $thumbnail->structure->getResource(),
        );

        $this->assetServer->serve($structure);
    }
}
