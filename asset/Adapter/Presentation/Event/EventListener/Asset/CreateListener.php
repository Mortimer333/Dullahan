<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Adapter\Presentation\Event\Transport\Create\CreateAssetEvent;
use Dullahan\Asset\Application\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Main\Service\UserService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class CreateListener
{
    /**
     * @TODO make interfaces for UserService
     */
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        private AssetPersistenceManagerInterface $assetPersistenceManager,
        private UserService $userService,
        protected EntityManagerInterface $entityManager,
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
