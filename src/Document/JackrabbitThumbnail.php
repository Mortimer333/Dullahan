<?php

declare(strict_types=1);

namespace Dullahan\Document;

use Dullahan\Asset\Manager\JackrabbitAssetManager;
use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Contract\AssetManager\ThumbnailInterface;
use Dullahan\Entity\AssetThumbnailPointer;
use PHPCR\PropertyType;

class JackrabbitThumbnail implements ThumbnailInterface
{
    public function __construct(
        protected ThumbnailInterface $entity,
        protected \Closure $nodeDecorator,
    ) {
    }

    public function getEntity(): ThumbnailInterface
    {
        return $this->entity;
    }

    public function getId(): ?int
    {
        return $this->getEntity()->getId();
    }

    /**
     * @return resource|null
     */
    public function getFile()
    {
        if (!isset($this->file)) {
            $this->file = $this->getNode()
                ->getNode(JackrabbitAssetManager::CONTENT_META_NAME)
                ->getPropertyValue(JackrabbitAssetManager::PROPERTY_FILE, PropertyType::BINARY)
            ;
        }

        return $this->file;
    }

    public function getAsset(): ?AssetInterface
    {
        return $this->getEntity()->getAsset();
    }

    public function setAsset(?AssetInterface $asset): self
    {
        return $this->getEntity()->setAsset($asset);
    }

    public function getName(): ?string
    {
        return $this->getEntity()->getName();
    }

    public function setName(string $name): self
    {
        return $this->getEntity()->setName($name);
    }

    public function getWeight(): ?int
    {
        return $this->getEntity()->getWeight();
    }

    public function setWeight(int $weight): self
    {
        return $this->getEntity()->setWeight($weight);
    }

    public function getSettings(): ?string
    {
        return $this->getEntity()->getSettings();
    }

    public function setSettings(string $settings): self
    {
        return $this->getEntity()->setSettings($settings);
    }

    public function getAssetPointers(): \Iterator
    {
        return $this->getEntity()->getAssetPointers();
    }

    public function addAssetPointer(AssetThumbnailPointer $assetPointer): self
    {
        return $this->getEntity()->addAssetPointer($assetPointer);
    }

    public function removeAssetPointer(AssetThumbnailPointer $assetPointer): self
    {
        return $this->getEntity()->removeAssetPointer($assetPointer);
    }
}
