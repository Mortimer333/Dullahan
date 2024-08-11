<?php

declare(strict_types=1);

namespace Dullahan\Main\Document;

use Doctrine\Common\Collections\Collection;
use Dullahan\Main\Asset\Manager\JackrabbitAssetManager;
use Dullahan\Main\Contract\AssetManager\AssetInterface;
use Dullahan\Main\Entity\Asset as AssetEntity;
use Dullahan\Main\Entity\AssetPointer;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Thumbnail\Adapter\Infrastructure\Database\Entity\Thumbnail;
use PHPCR\NodeInterface;
use PHPCR\PropertyType;
use Thumbnail\Application\Port\Presentation\ThumbnailInterface;

class JackrabbitAsset implements AssetInterface
{
    protected bool $requiresFlush = false;
    protected bool $isDirty = false;
    protected $file;

    public function __construct(
        protected AssetEntity $entity,
        protected \Closure $nodeDecorator,
        protected \Closure $lazyLoadParent,
        protected \Closure $lazyLoadChildren,
    ) {
    }

    public function getOwner(): ?User
    {
        return $this->entity->getUser();
    }

    public function getEntity(): AssetInterface
    {
        return $this->entity;
    }

    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    public function getPath(): ?string
    {
        return $this->entity->getPath();
    }

    public function getName(): ?string
    {
        return ($this->nodeDecorator)()->getName();
    }

    public function getExtension(): ?string
    {
        return $this->entity->getExtension();
    }

    public function getMimeType(): ?string
    {
        return $this->entity->getMimeType();
    }

    public function getWeight(): ?int
    {
        return $this->entity->getWeight();
    }

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

    /**
     * @return Collection<int, AssetPointer>
     */
    public function getPointers(): \IteratorAggregate&\Countable
    {
        return $this->entity->getPointers();
    }

    public function addPointer(AssetPointer $pointer): self
    {
        $this->requiresFlush = true;
        $this->entity->addPointer($pointer);

        return $this;
    }

    public function removePointer(AssetPointer $pointer): self
    {
        $this->requiresFlush = true;
        $this->entity->removePointer($pointer);

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->entity->getCreated();
    }

    public function createdBy(): ?User
    {
        return $this->entity->createdBy();
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->entity->getModified();
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->requiresFlush = true;
        $this->entity->setModified($modified);

        return $this;
    }

    public function modifiedBy(): ?User
    {
        return $this->entity->modifiedBy();
    }

    /**
     * @param ?array<string> $nameFilter
     *
     * @return Collection<string, mixed>
     */
    public function getProperties(?array $nameFilter = null): \IteratorAggregate&\Countable
    {
        $arr = [];
        foreach (($this->getNode()->getProperties($nameFilter) ?? []) as $property) {
            $arr[$property->getName()] = $property->getValue();
        }

        return $arr;
    }

    public function getProperty(string $name, mixed $default = null): mixed
    {
        return $this->getNode()->getPropertyValueWithDefault($name, $default) ?? $default;
    }

    public function setProperty(string $name, mixed $value): self
    {
        $this->getNode()->setProperty($name, $value);
        $this->markAsDirty();

        return $this;
    }

    public function removeProperty(string $name): self
    {
        $this->getNode()->setProperty($name, null);
        $this->markAsDirty();

        return $this;
    }

    public function markAsDirty(): self
    {
        $this->isDirty = true;

        return $this;
    }

    public function markAsClean(): self
    {
        $this->isDirty = false;

        return $this;
    }

    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    public function requiresFlush(): bool
    {
        return $this->requiresFlush;
    }

    public function resetFlush(): self
    {
        $this->requiresFlush = false;

        return $this;
    }

    public function getParent(): ?AssetInterface
    {
        return ($this->lazyLoadParent)();
    }

    public function getChildren(?string $nameMatch = null, ?string $typeMatch = null): \IteratorAggregate&\Countable
    {
        return ($this->lazyLoadChildren)();
    }

    protected function getNode(): NodeInterface
    {
        return ($this->nodeDecorator)();
    }
}