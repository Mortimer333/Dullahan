<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Generator;

use Dullahan\Main\Contract\AssetAwareInterface;
use DullahanMainContract\AssetManager\AssetInterface;
use DullahanMainEntity\AssetPointer;
use DullahanMainEntity\AssetThumbnailPointer;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailRetrieveInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailGeneratorInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailInterface;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use PHPImageWorkshop\ImageWorkshop;

class ImageWorkshopThumbnailGenerator implements ThumbnailGeneratorInterface
{
    public function __construct(
        protected ThumbnailRetrieveInterface $thumbnailRetrieve,
    ) {
    }

    public const SUPPORTED_MIME_TYPES = [
        'image/jpeg' => true,
        'image/png' => true,
        'image/webp' => true,
        // Gif not supported but could be
        // https://phpimageworkshop.com/tutorial/5/manage-animated-gif-with-imageworkshop.html
    ];

    public function generate(
        AssetAwareInterface $entity,
        ThumbnailConfig $config
    ): ?ThumbnailInterface {
        $pointer = $config->assetPointer;
        if (
            !$pointer instanceof AssetPointer
            || !isset(self::SUPPORTED_MIME_TYPES[$pointer->getAsset()?->getMimeType()])
        ) {
            return null;
        }

        return $this->generateThumbnailFile($pointer->getAsset(), $config);
    }

    /**
     * @TODO catch those and replace with common one
     * @throws \PHPImageWorkshop\Core\Exception\ImageWorkshopLayerException
     * @throws \PHPImageWorkshop\Exception\ImageWorkshopException
     *
     * @return resource
     */
    public function generateThumbnailFile(
        AssetInterface $asset,
        ThumbnailConfig $config,
    ) {
        // We are creating thumbnail based on existing asset, so we can skip any validation
        $layer = ImageWorkshop::initFromResourceVar($asset->getFile());
        $layer->resizeInPixel(
            $config->width,
            $config->height,
            (bool) $config->autoResize,
        );
        if (!empty($config->crop)) {
            $width = $layer->getWidth();
            $height = $layer->getHeight();
            [$cropWidth, $cropHeight, $posX, $posY, $position] = $config->crop;

            $layer->cropInPixel(
                $width > $cropWidth ? $cropWidth : $width,
                $height > $cropHeight ? $cropHeight : $height,
                $posX,
                $posY,
                $position,
            );
        }

        return $layer->getResult();
    }

//    protected function replaceThumbnails(AssetInterface $asset): void
//    {
//        foreach ($asset->getThumbnails() as $thumbnail) {
//            if (is_file($thumbnail->getFullPath())) {
//                unlink($thumbnail->getFullPath());
//            }
//            $this->generateThumbnailFile(
//                $asset,
//                json_decode($thumbnail->getSettings() ?: '', true) ?: [],
//                (string) $thumbnail->getName()
//            );
//        }
//    }
}
