<?php

declare(strict_types=1);

namespace Dullahan\Document;

use Doctrine\Common\Collections\Collection;
use Dullahan\AssetManager\JackrabbitAssetManager;
use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Entity\Asset as AssetEntity;
use Dullahan\Entity\AssetPointer;
use Dullahan\Entity\Thumbnail;
use Dullahan\Entity\User;
use PHPCR\AccessDeniedException;
use PHPCR\Lock\LockException;
use PHPCR\NodeInterface;
use PHPCR\NodeType\ConstraintViolationException;
use PHPCR\PropertyType;
use PHPCR\RepositoryException;
use PHPCR\Version\VersionException;

class JackrabbitAsset implements AssetInterface
{
    protected bool $requiresFlush = false;
    protected bool $isDirty = false;
    protected bool $remove = false;

    public function __construct(
        protected AssetEntity   $entity,
        protected NodeInterface $node,
    ) {
    }

    /**
     * @internal
     */
    public function getNode(): NodeInterface
    {
        return $this->node;
    }

    /**
     * @internal
     */
    public function getEntity(): AssetEntity
    {
        return $this->entity;
    }

    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    public function getPath(): ?string
    {
        return $this->node->getPath();
    }

    public function getName(): ?string
    {
        return $this->node->getName();
    }

    public function getExtension(): ?string
    {
        return $this->getPropertyValue(JackrabbitAssetManager::PROPERTY_EXTENSION, PropertyType::STRING);
    }

    public function getMimeType(): ?string
    {
        return $this->getPropertyValue(JackrabbitAssetManager::PROPERTY_MIME_TYPE, PropertyType::STRING);
    }

    public function getWeight(): ?int
    {
        return $this->getPropertyValue(JackrabbitAssetManager::PROPERTY_WEIGHT, PropertyType::LONG);
    }

    public function getFile()
    {
        return $this->getPropertyValue(JackrabbitAssetManager::PROPERTY_FILE, PropertyType::BINARY);
    }

    /**
     * @inheritDoc
     */
    public function getPointers(): Collection
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

    /**
     * @inheritDoc
     */
    public function getThumbnails(): Collection
    {
        return $this->entity->getThumbnails();
    }

    public function addThumbnail(Thumbnail $thumbnail): self
    {
        $this->requiresFlush = true;
        $this->entity->addThumbnail($thumbnail);

        return $this;
    }

    public function removeThumbnail(Thumbnail $thumbnail): self
    {
        $this->requiresFlush = true;
        $this->entity->removeThumbnail($thumbnail);

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
     * @return array<string, mixed>
     */
    public function getProperties($nameFilter = null): \Iterator
    {
        foreach ($this->node->getProperties($nameFilter) as $property) {
            yield $property->getName() => $property->getValue();
        }
    }

    public function getProperty(string $name, mixed $default = null): mixed
    {
        return $this->node->getPropertyValueWithDefault($name, $default);
    }

    public function setProperty(string $name, mixed $value): self
    {
        $this->node->setProperty($name, $value);
        $this->markAsDirty();

        return $this;
    }

    /**
     * @throws VersionException
     * @throws LockException
     * @throws ConstraintViolationException
     * @throws AccessDeniedException
     * @throws RepositoryException
     */
    public function removeProperty(string $name): self
    {
        $this->node->setProperty($name, null);
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

    public function markToRemove(bool $remove): bool
    {
        $this->remove = $remove;

        return true;
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

    protected function getPropertyValue(string $name, ?int $type = null): mixed
    {
        if (!$this->node->hasProperty($name)) {
            return null;
        }

        return $this->node->getPropertyValue($name, $type);
    }
}
