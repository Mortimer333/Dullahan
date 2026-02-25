<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Create\CreateAssetEvent;
use Dullahan\User\Adapter\Symfony\Application\UserRetrieveService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class CreateListener
{
    /**
     * @TODO make interfaces for UserService
     */
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
        private UserRetrieveService $userService,
    ) {
    }

    #[AsEventListener(event: CreateAssetEvent::class)]
    public function onCreateAsset(CreateAssetEvent $event): void
    {
        $structure = $this->assetFileManager->upload($event->getNewStructure());
        $event->setCreatedFile($structure);
        $user = $this->userService->getLoggedInUser();
        $event->setEntity($this->assetPersistenceManager->create($structure, $user));
    }
}
