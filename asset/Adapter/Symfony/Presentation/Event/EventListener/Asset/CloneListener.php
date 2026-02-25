<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Clone\CloneAssetEvent;
use Dullahan\User\Adapter\Symfony\Application\UserRetrieveService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class CloneListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
        private UserRetrieveService $userService,
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
