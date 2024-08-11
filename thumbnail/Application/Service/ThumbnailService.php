<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Application\Service;

use Dullahan\Main\Contract\AssetAwareInterface;
use DullahanMainService\Util\BinUtilService;
use Dullahan\Thumbnail\Adapter\Infrastructure\Database\Entity\Thumbnail;
use Dullahan\Thumbnail\Application\Port\Infrastructure\Database\Repository\ThumbnailRetrieveInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailGeneratorInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailMapperInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailServiceInterface;

final readonly class ThumbnailService implements ThumbnailServiceInterface
{
    public function __construct(
        protected ThumbnailMapperInterface $thumbnailMapper,
        protected ThumbnailGeneratorInterface $thumbnailGenerator,
        protected ThumbnailRetrieveInterface $thumbnailRetrieve,
    ) {
    }

    public function generate(AssetAwareInterface $asset, string $fieldName): array|\Generator
    {
        foreach ($this->thumbnailMapper->mapField($asset, $fieldName) as $config) {
            $pointer = $config->assetPointer;
            $asset = $pointer?->getAsset();
            if (!$asset) {
                continue;
            }

            $exitingThumbnail = $this->thumbnailRetrieve->findSame($asset, $config);
            if ($exitingThumbnail) {
                $assetThumbnailPointer = new AssetThumbnailPointer();
                $assetThumbnailPointer->setAssetPointer($pointer)
                    ->setThumbnail($exitingThumbnail)
                    ->setCode($config->getCode())
                ;
                $pointer->addThumbnail($assetThumbnailPointer);

                return $exitingThumbnail;
            }

            $filename = $config->getFingerPrint() . '.' . $asset->getExtension();
            $thumbFile = $this->thumbnailGenerator->generate($asset, $config);
            $stats = fstat($thumbFile);
            BinUtilService::logToTest($stats);
            throw new \Exception('Stop');

            $thumbnail = new Thumbnail();
            $thumbnail->setAsset($asset)
                ->setName($filename)
                ->setWeight()
                ->setSettings((string) $config)
            ;

            // How do we implement asset manager here? Separate ThumbnailAssetManager?

            $assetThumbnailPointer = new AssetThumbnailPointer();
            $assetThumbnailPointer->setCode($config->getCode());

            $thumbnail->addAssetPointer($assetThumbnailPointer);
            $pointer->addThumbnail($assetThumbnailPointer);

            yield $thumbnail;
        }
    }
}
