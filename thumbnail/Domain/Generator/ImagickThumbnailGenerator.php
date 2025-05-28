<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain\Generator;

use Dullahan\Asset\Domain\Exception\AssetNotFoundException;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Asset\Port\Presentation\AssetServiceInterface;
use Dullahan\Thumbnail\Domain\Exception\ThumbnailCannotBeGeneratedException;
use Dullahan\Thumbnail\Domain\ThumbnailConfig;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailGeneratorInterface;

class ImagickThumbnailGenerator implements ThumbnailGeneratorInterface
{
    public const SUPPORTED_MIME_TYPES = [
        'image/jpeg' => true,
        'image/png' => true,
        'image/webp' => true,
        // Gif not supported but could be
        // https://phpimageworkshop.com/tutorial/5/manage-animated-gif-with-imageworkshop.html
    ];

    public function __construct(
        protected AssetServiceInterface $assetService,
    ) {
    }

    public function generate(
        ThumbnailConfig $config,
        string $filename,
    ) {
        try {
            $asset = $this->assetService->get($config->assetId);
        } catch (AssetNotFoundException) {
            throw new ThumbnailCannotBeGeneratedException(sprintf(
                'Thumbnail %s could\'t be generated because related asset was not found',
                $config->code,
            ), 404);
        }

        if (!isset(self::SUPPORTED_MIME_TYPES[$asset->structure->mimeType])) {
            throw new ThumbnailCannotBeGeneratedException(sprintf(
                'Thumbnail %s for %s could\'t be generated because of unsupported mime type: "%s"',
                $config->code,
                $asset->structure->path,
                $asset->structure->mimeType,
            ), 500);
        }

        return $this->generateThumbnailFile($asset->structure, $config);
    }

    /**
     * @return resource
     */
    protected function generateThumbnailFile(
        Structure $structure,
        ThumbnailConfig $config,
    ) {
        if (!$structure->getResource()) {
            throw new ThumbnailCannotBeGeneratedException(sprintf(
                'Thumbnail %s for %s could\'t be generated because file was not found',
                $config->code,
                $structure->path,
            ), 404);
        }

        $width = $config->width;
        $height = $config->height;

        $resource = $structure->getResource();

        $imagick = new \Imagick();
        $tmp = tmpfile();
        // @TODO possibility for optimization when thumbnailing the same file in different ways - do copy only once
        stream_copy_to_stream($resource, $tmp);
        rewind($tmp);
        $imagick->readImageFile($tmp);

        $success = $imagick->resizeImage(
            $width ?? $imagick->getImageWidth(),
            $height ?? $imagick->getImageHeight(),
            \Imagick::FILTER_CATROM,
            .9,
            (bool) $config->autoResize,
        );

        if (!$success) {
            throw new \Exception('no success');
        }

        $cropWidth = $imagick->getImageWidth();
        $cropHeight = $imagick->getImageHeight();

        if (!empty($config->crop)) {
            [$newWidth, $newHeight, $posX, $posY, $position] = $config->crop;
            [$startingX, $startingY] = $this->decideOnStartingPoint($cropWidth, $cropHeight, $position);

            $imagick->cropimage(
                $newWidth,
                $newHeight,
                $startingX + $posX,
                $startingY + $posY
            );
        }

        $imagick->writeImageFile($tmp);
        $imagick->destroy();

        return $tmp;
    }

    /**
     * @return array<int>
     */
    protected function decideOnStartingPoint(int $width, int $height, string $type): array
    {
        if (2 !== strlen($type)) {
            // @TODO add exception
            throw new \Exception('Invalid type');
        }

        return [$this->findStartingPoint($width, $type[0]), $this->findStartingPoint($height, $type[1])];
    }

    protected function findStartingPoint(int $value, string $type): int
    {
        return match ($type) {
            'M' => (int) ($value / 2),
            'S' => 0,
            'E' => $value,
            default => 0,
        };
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
