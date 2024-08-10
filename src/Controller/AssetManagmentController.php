<?php

declare(strict_types=1);

namespace Dullahan\Controller;

use Dullahan\Asset\UrlResolver\JackrabbitUrlResolver;
use Dullahan\Contract\AssetManager\AssetManagerInterface;
use Dullahan\Contract\AssetManager\AssetServerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as SWG;

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
}
