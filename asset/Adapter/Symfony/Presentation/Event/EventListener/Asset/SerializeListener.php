<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Port\Presentation\AssetRetrievalManagerInterface;
use Dullahan\Asset\Port\Presentation\AssetUrlResolverInterface;
use Dullahan\Asset\Presentation\Event\Transport\Serialize\AssetPointerSerializeEvent;
use Dullahan\Asset\Presentation\Event\Transport\Serialize\AssetSerializeEvent;
use Dullahan\Main\Service\Util\FileUtilService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class SerializeListener
{
    public function __construct(
        private AssetUrlResolverInterface $assetUrlResolver,
        private AssetRetrievalManagerInterface $assetRetrievalManager,
    ) {
    }

    #[AsEventListener(event: AssetSerializeEvent::class)]
    public function assetSerialize(AssetSerializeEvent $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $asset = $event->asset;
        /** @var \Dullahan\Asset\Domain\Entity\Asset $entity */
        $entity = $asset->entity;
        $structure = $asset->structure;

        $event->serialized = [
            'id' => (int) $entity->getId(),
            'name' => $structure->name,
            'extension' => (string) $structure->extension,
            'src' => $this->assetUrlResolver->getUrl($asset),
            'weight' => (int) ($structure->weight ?: $entity->getWeight()),
            'mime_type' => (string) ($structure->mimeType ?: $entity->getMimeType()),
            'weight_readable' => FileUtilService::humanFilesize((int) $structure->weight),
            'pointers_amount' => count($entity->getPointers()),
            'path' => $entity->getFullPath(),
        ];
    }

    #[AsEventListener(event: AssetPointerSerializeEvent::class)]
    public function assetPointerSerialize(AssetPointerSerializeEvent $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $asset = $this->assetRetrievalManager->get($event->assetPointer->getAsset()?->getId());

        $event->serialized = [
            'id' => $asset->entity->getId(),
            'src' => $this->assetUrlResolver->getUrl($asset),
            'name' => $asset->structure->name,
            'weight' => (int) $asset->structure->weight,
            'weight_readable' => FileUtilService::humanFilesize((int) $asset->structure->weight),
            'extension' => (string) $asset->structure->extension,
        ];
    }
}
