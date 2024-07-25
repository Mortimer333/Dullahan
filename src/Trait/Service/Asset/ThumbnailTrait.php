<?php

declare(strict_types=1);

namespace Dullahan\Trait\Service\Asset;

use Dullahan\Attribute\Thumbnail as ThumbnailAttribute;
use Dullahan\Contract\AssetAwareInterface;
use Dullahan\Entity\Asset;
use Dullahan\Entity\AssetPointer;
use Dullahan\Entity\AssetThumbnailPointer;
use Dullahan\Entity\Thumbnail;
use PHPImageWorkshop\Core\ImageWorkshopLayer;
use PHPImageWorkshop\ImageWorkshop;

trait ThumbnailTrait
{
    /**
     * @param array{width: int|null, height: int|null, autoResize: bool|null, crop: array<mixed>} $settings
     *
     * @throws \PHPImageWorkshop\Core\Exception\ImageWorkshopLayerException
     * @throws \PHPImageWorkshop\Exception\ImageWorkshopException
     */
    public function generateThumbnailFile(Asset $asset, array $settings, string $filename): ImageWorkshopLayer
    {
        // We are creating thumbnail based on existing asset, so we can skip any validation
        $layer = ImageWorkshop::initFromPath($asset->getFullPath());
        $layer->resizeInPixel($settings['width'], $settings['height'], $settings['autoResize'] ?: false);
        if (!empty($settings['crop'])) {
            $width = $layer->getWidth();
            $height = $layer->getHeight();
            [$cropWidth, $cropHeight, $posX, $posY, $position] = $settings['crop'];

            $layer->cropInPixel(
                $width > $cropWidth ? $cropWidth : $width,
                $height > $cropHeight ? $cropHeight : $height,
                $posX,
                $posY,
                $position,
            );
        }

        /**
         * @TODO
         * Later when we will have separate service/server for images think on a way to integrate
         * ImageWorkshop with assetService to have one function to save images
         */
        $imageQuality = 95; // useless for GIF, useful for PNG and JPEG (0 to 100%)
        $layer->save(
            $asset->getFullPathWithoutName(),
            $filename . '.' . $asset->getExtension(),
            false,
            null,
            $imageQuality
        );

        return $layer;
    }

    public function createThumbnails(AssetAwareInterface $entity, string $fieldName): void
    {
        $property = new \ReflectionProperty($entity, $fieldName);
        $assets = $property->getAttributes(ThumbnailAttribute::class);

        foreach ($assets as $asset) {
            $assetAttribute = $asset->newInstance();
            $this->createThumbnail($entity, $fieldName, $assetAttribute);
        }
    }

    protected function createThumbnail(
        AssetAwareInterface $entity,
        string $fieldName,
        ThumbnailAttribute $assetAttribute
    ): void {
        $pointer = $entity->{'get' . ucfirst($fieldName)}();
        if (!$pointer instanceof AssetPointer || 'image' !== $pointer->getAsset()?->getMimeType()) {
            return;
        }

        $asset = $pointer->getAsset();
        $settings = json_encode((array) $assetAttribute) ?: '';
        $exitingThumbnail = $this->em->getRepository(Thumbnail::class)->findOneBy([
            'asset' => $asset,
            'settings' => $settings,
        ]);

        if ($exitingThumbnail) {
            $assetThumbnailPointer = new AssetThumbnailPointer();
            $assetThumbnailPointer->setAssetPointer($pointer)
                ->setThumbnail($exitingThumbnail)
                ->setCode($assetAttribute->code)
            ;
            $pointer->addThumbnail($assetThumbnailPointer);

            $this->em->persist($assetThumbnailPointer);

            return;
        }

        $filename = $this->getUniqueAssetName(
            $asset->getFullPathWithoutName(),
            (string) $asset->getExtension()
        );
        $this->generateThumbnailFile($asset, (array) $assetAttribute, $filename);

        $thumbnail = new Thumbnail();
        $thumbnail->setAsset($asset)
            ->setName($filename)
            ->setWeight((int) filesize($thumbnail->getFullPath()))
            ->setSettings($settings)
        ;

        $assetThumbnailPointer = new AssetThumbnailPointer();
        $assetThumbnailPointer->setCode($assetAttribute->code);

        $thumbnail->addAssetPointer($assetThumbnailPointer);
        $pointer->addThumbnail($assetThumbnailPointer);

        $this->em->persist($thumbnail);
        $this->em->persist($assetThumbnailPointer);
    }

    protected function replaceThumbnails(Asset $asset): void
    {
        foreach ($asset->getThumbnails() as $thumbnail) {
            if (is_file($thumbnail->getFullPath())) {
                unlink($thumbnail->getFullPath());
            }
            $this->generateThumbnailFile(
                $asset,
                json_decode($thumbnail->getSettings() ?: '', true) ?: [],
                (string) $thumbnail->getName()
            );
        }
    }
}
