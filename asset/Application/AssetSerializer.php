<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application;

use Dullahan\Asset\Application\Port\Presentation\AssetPointerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetSerializerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetUrlResolverInterface;
use Dullahan\Asset\Domain\Asset;
use Dullahan\Main\Service\Util\FileUtilService;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailServiceInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailUrlResolverInterface;
use Dullahan\Thumbnail\Domain\Thumbnail;
use Dullahan\Thumbnail\Entity\AssetThumbnailPointer;

/**
 * @TODO separate thumbnail service (by refactoring functionality to be Even based)
 */
class AssetSerializer implements AssetSerializerInterface
{
    public function __construct(
        protected AssetUrlResolverInterface $assetUrlResolver,
        protected ThumbnailServiceInterface $thumbnailService,
        protected ThumbnailUrlResolverInterface $thumbnailUrlResolver,
        protected AssetServiceInterface $assetService,
    ) {
    }

    public function serialize(Asset $asset): array
    {
        $thumbnails = [];
        foreach ($this->thumbnailService->getThumbnails($asset) as $thumbnail) {
            $thumbnails[] = $this->serializeThumbnail($thumbnail);
        }

        $entity = $asset->entity;
        $structure = $asset->structure;

        return [
            'id' => (int) $entity->getId(),
            'name' => $structure->name,
            'extension' => (string) $structure->extension,
            'src' => $this->assetUrlResolver->getUrl($asset),
            'weight' => (int) $structure->weight,
            'weight_readable' => FileUtilService::humanFilesize((int) $structure->weight),
            'thumbnails' => $thumbnails,
            'pointers_amount' => count($entity->getPointers()),
        ];
    }

    /**
     * @TODO move to thumbnail bundle
     */
    public function serializeThumbnail(Thumbnail $thumbnail): array
    {
        $entity = $thumbnail->entity;
        $structure = $thumbnail->structure;
        $settings = json_decode($entity->getSettings() ?: '{width: null, height: null}', true);
        $dimensions = [
            'width' => $settings['crop'][0] ?? $settings['width'] ?? 'auto',
            'height' => $settings['crop'][1] ?? $settings['height'] ?? 'auto',
        ];

        $pointers = [];
        /** @var AssetThumbnailPointer $assetPointer */
        foreach ($entity->getAssetPointers() as $assetPointer) {
            $pointer = $assetPointer->getAssetPointer();
            if (!$pointer) {
                continue;
            }
            $pointers[(string) $assetPointer->getCode()] = [
                'id' => (int) $pointer->getId(),
            ];
        }

        return [
            'id' => (int) $entity->getId(),
            'src' => $this->thumbnailUrlResolver->getUrl($thumbnail),
            'name' => $structure->name,
            'weight' => (int) $structure->weight,
            'weight_readable' => FileUtilService::humanFilesize((int) $structure->weight),
            'pointers' => $pointers,
            'dimensions' => $dimensions,
        ];
    }

    public function serializePointer(AssetPointerInterface $pointer): array
    {
        $thumbnails = [];
        foreach ($this->thumbnailService->getThumbnailsByPointer($pointer) as $thumbnail) {
            foreach ($thumbnail->entity->getAssetPointers() as $assetPointer) {
                $thumbnails[(string) $assetPointer->getCode()] = $this->thumbnailUrlResolver->getUrl($thumbnail);
            }
        }

        $asset = $this->assetService->get($pointer->getAsset()?->getId());

        return [
            'id' => $asset->entity->getId(),
            'src' => $this->assetUrlResolver->getUrl($asset),
            'name' => $asset->structure->name,
            'weight' => (int) $asset->structure->weight,
            'weight_readable' => FileUtilService::humanFilesize((int) $asset->structure->weight),
            'extension' => (string) $asset->structure->extension,
            'thumbnails' => $thumbnails,
        ];
    }
}
