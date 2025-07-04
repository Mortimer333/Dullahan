<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Directory;
use Dullahan\Asset\Port\Infrastructure\AssetFileManagerInterface;
use Dullahan\Asset\Presentation\Event\Transport\Create\CreateAssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class RecursiveCreateListener
{
    public function __construct(
        private AssetFileManagerInterface $assetFileManager,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener(event: CreateAssetEvent::class, priority: 10)]
    public function onCreateAsset(CreateAssetEvent $event): void
    {
        if (!$event->getContext()->hasProperty(AssetFileManagerInterface::RECURSIVE)) {
            return;
        }
        $this->recursiveCreateFolders($event->getNewStructure()->getPath());
    }

    protected function recursiveCreateFolders(string $path): void
    {
        if ($this->assetFileManager->exists($path)) {
            return;
        }

        $this->recursiveCreateFolders(dirname($path));
        $this->assetFileManager->upload(new Directory($path));
    }
}
