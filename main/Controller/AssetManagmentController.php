<?php

declare(strict_types=1);

namespace Dullahan\Main\Controller;

use Dullahan\Main\Asset\UrlResolver\JackrabbitUrlResolver;
use Dullahan\Main\Contract\AssetManager\AssetManagerInterface;
use Dullahan\Main\Contract\AssetManager\AssetServerInterface;
use Dullahan\Thumbnail\Adapter\Presentation\UrlResolver\JackrabbitThumbnailUrlResolver;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project Asset Management')]
#[Route('/asset')]
class AssetManagmentController extends AbstractController
{
    public function __construct(
        protected AssetManagerInterface $assetManager,
        protected AssetServerInterface $assetServer,
    ) {
    }

    #[Route('/{id<\d+>}/jackrabbit', name: JackrabbitUrlResolver::IMAGE_PATH_NAME, methods: 'GET')]
    public function get(int $id): void
    {
        $this->assetServer->serve($this->assetManager->get($id));
    }

    #[Route(
        '/thumbnail/{id<\d+>}/jackrabbit',
        name: JackrabbitThumbnailUrlResolver::RETRIEVE_PATH_NAME,
        methods: 'GET',
    )]
    public function getThumbnail(int $id): void
    {
        $this->assetServer->serve($this->assetManager->get($id));
    }
}
