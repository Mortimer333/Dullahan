<?php

declare(strict_types=1);

namespace Dullahan\Asset;

use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Contract\AssetManager\AssetPointerInterface;
use Dullahan\Contract\AssetManager\AssetSerializerInterface;
use Dullahan\Contract\AssetManager\AssetUrlResolverInterface;
use Dullahan\Contract\AssetManager\ThumbnailInterface;
use Dullahan\Service\Util\FileUtilService;

class AssetSerializer implements AssetSerializerInterface
{
    public function __construct(
        protected AssetUrlResolverInterface $assetUrlResolver,
    ) {
    }

    public function serialize(AssetInterface $asset): array
    {
        $thumbnails = [];
        foreach ($asset->getThumbnails() as $thumbnail) {
            $thumbnails[] = $this->serializeThumbnail($thumbnail);
        }

        return [
            'id' => (int) $asset->getId(),
            'name' => (string) $asset->getName(),
            'extension' => (string) $asset->getExtension(),
            'src' => $this->assetUrlResolver->getUrl($asset),
            'weight' => (int) $asset->getWeight(),
            'weight_readable' => FileUtilService::humanFilesize((int) $asset->getWeight()),
            'thumbnails' => $thumbnails,
            'pointers_amount' => count($asset->getPointers()),
        ];
    }

    public function serializeThumbnail(ThumbnailInterface $thumbnail): array
    {
        $settings = json_decode($thumbnail->getSettings() ?: '{width: null, height: null}', true);
        $dimensions = [
            'width' => $settings['crop'][0] ?? $settings['width'] ?? 'auto',
            'height' => $settings['crop'][1] ?? $settings['height'] ?? 'auto',
        ];

        $pointers = [];
        foreach ($thumbnail->getAssetPointers() as $assetPointer) {
            $pointer = $assetPointer->getAssetPointer();
            if (!$pointer) {
                continue;
            }
            $pointers[(string) $assetPointer->getCode()] = [
                'id' => (int) $pointer->getId(),
                'class' => (string) $pointer->getEntityClass(),
                'column' => (string) $pointer->getEntityColumn(),
                'entity' => (int) $pointer->getEntityId(),
            ];
        }

        return [
            'id' => (int) $thumbnail->getId(),
            'src' => $this->assetUrlResolver->getUrl($thumbnail),
            'name' => (string) $thumbnail->getName(),
            'weight' => (int) $thumbnail->getWeight(),
            'weight_readable' => FileUtilService::humanFilesize((int) $thumbnail->getWeight()),
            'pointers' => $pointers,
            'dimensions' => $dimensions,
        ];
    }

    public function serializePointer(AssetPointerInterface $asset): array
    {
        $thumbnails = [];
        foreach ($asset->getThumbnailPointers() as $thumbnailPointer) {
            $thumbnails[$thumbnailPointer->getCode()] = $thumbnailPointer->getThumbnail()?->getURL();
        }

        return [
            'id' => $asset->getAsset()?->getId(),
            'src' => $asset->getAsset()?->getURL(),
            'name' => $asset->getAsset()?->getName(),
            'weight' => $asset->getAsset()?->getWeight(),
            'weight_readable' => FileUtilService::humanFilesize((int) $asset->getAsset()?->getWeight()),
            'extension' => $asset->getAsset()?->getExtension(),
            'thumbnails' => $thumbnails,
        ];
    }
}
