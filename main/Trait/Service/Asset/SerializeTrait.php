<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait\Service\Asset;

use Dullahan\Main\Entity\Asset;
use Dullahan\Main\Entity\AssetPointer;
use Dullahan\Main\Service\Util\FileUtilService;
use Dullahan\Main\Thumbnail\Adapter\Infrastructure\Database\Entity\Thumbnail;

trait SerializeTrait
{
    /**
     * @return array<string, int|string|array<array<string, mixed>|string|int|null>|null>
     */
    public function serialize(Asset $asset): array
    {
        $thumbnails = [];
        foreach ($asset->getThumbnails() as $thumbnail) {
            $thumbnails[] = $this->serializeThumbnail($thumbnail);
        }

        return [
            'id' => $asset->getId(),
            'name' => $asset->getName(),
            'extension' => $asset->getExtension(),
            'src' => $asset->getURL(),
            'weight' => $asset->getWeight(),
            'weight_readable' => FileUtilService::humanFilesize((int) $asset->getWeight()),
            'thumbnails' => $thumbnails,
            'pointers_amount' => $asset->getPointers()->count(),
        ];
    }

    /**
     * @return array<string, array<string, array<string, int|string|null>>|string|int|null>
     */
    public function serializeThumbnail(Thumbnail $thumbnail): array
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
            $pointers[$assetPointer->getCode()] = [
                'id' => $pointer->getId(),
                'class' => $pointer->getEntityClass(),
                'column' => $pointer->getEntityColumn(),
                'entity' => $pointer->getEntityId(),
            ];
        }

        return [
            'id' => $thumbnail->getId(),
            'src' => $thumbnail->getURL(),
            'name' => $thumbnail->getName(),
            'weight' => $thumbnail->getWeight(),
            'weight_readable' => FileUtilService::humanFilesize((int) $thumbnail->getWeight()),
            'pointers' => $pointers,
            'dimensions' => $dimensions,
        ];
    }

    /**
     * @return array<string, int|string|array<string, string|null>|null>
     */
    public function serializePointer(AssetPointer $asset): array
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
