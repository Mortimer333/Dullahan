<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Adapter\Presentation\Event\Transport\Clone\CloneAssetEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Domain\Asset;
use Dullahan\Main\Service\UserService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class CloneListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
        private UserService $userService,
    ) {
    }

    #[AsEventListener(event: CloneAssetEvent::class)]
    public function onMoveAsset(CloneAssetEvent $event): void
    {
        $asset = $event->getAsset();
        $path = $event->getPath();
        $clone = $this->assetFileManager->clone($asset->structure, $path);
        $entity = $this->assetPersistenceManager->create($clone, $this->userService->getLoggedInUser());
        $event->setAsset(
            new Asset(
                $clone,
                $entity,
                $asset->context,
            )
        );
    }
}
