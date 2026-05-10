<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\Asset\Presentation\Event\Transport\Serialize\AssetPointerSerializeEvent;
use Dullahan\Asset\Presentation\Event\Transport\Serialize\AssetSerializeEvent;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailServiceInterface;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailUrlResolverInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class AssetSerializeListener
{
    public function __construct(
        private ThumbnailServiceInterface $thumbnailService,
        private ThumbnailUrlResolverInterface $thumbnailUrlResolver,
    ) {
    }

    #[AsEventListener(event: AssetSerializeEvent::class, priority: 10)]
    public function assetSerialize(AssetSerializeEvent $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $thumbnails = [];
        foreach ($this->thumbnailService->getThumbnails($event->asset) as $thumbnail) {
            $thumbnails[] = $this->thumbnailService->serialize($thumbnail);
        }

        $event->serialized['thumbnails'] = $thumbnails;
    }

    #[AsEventListener(event: AssetPointerSerializeEvent::class, priority: 10)]
    public function assetPointerSerialize(AssetPointerSerializeEvent $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $thumbnails = [];
        foreach ($this->thumbnailService->getThumbnailsByPointer($event->assetPointer) as $thumbnail) {
            foreach ($thumbnail->entity->getAssetPointers() as $assetPointer) {
                $thumbnails[(string) $assetPointer->getCode()] = $this->thumbnailUrlResolver->getUrl($thumbnail);
            }
        }

        $event->serialized['thumbnails'] = $thumbnails;
    }
}
