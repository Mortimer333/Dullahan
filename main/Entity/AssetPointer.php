<?php

namespace Dullahan\Main\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Main\Contract\AssetAwareInterface;
use Dullahan\Main\Contract\PointerInterface;
use Dullahan\Main\Repository\AssetPointerRepository;

#[ORM\Entity(repositoryClass: AssetPointerRepository::class)]
class AssetPointer implements PointerInterface
{
    private ?AssetAwareInterface $entity = null;

    /**
     * @var EntityRepository<object>|null
     */
    private ?EntityRepository $entityRepository = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $entityClass = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\ManyToOne(inversedBy: 'pointers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\Column(length: 255)]
    private ?string $entityColumn = null;

    /**
     * @var Collection<int, AssetThumbnailPointer>
     */
    #[ORM\OneToMany(
        mappedBy: 'assetPointer',
        targetEntity: AssetThumbnailPointer::class,
        orphanRemoval: true,
        cascade: ['persist'],
    )]
    private Collection $thumbnailPointers;

    public function __construct()
    {
        $this->thumbnailPointers = new ArrayCollection();
    }

    public function getOrigin(): ?object
    {
        return $this->getAsset();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @template T of object
     *
     * @param EntityRepository<T> $repository
     */
    public function setEntityRepository(EntityRepository $repository): object
    {
        $this->entityRepository = $repository;

        return $this;
    }

    public function getEntity(): ?object
    {
        if (!$this->entity) {
            $entity = $this->entityRepository?->find((int) $this->getEntityId());
            if (!$entity instanceof AssetAwareInterface) {
                throw new \Exception("Chosen entity doesn't implement AssetAwareInterface", 500);
            }
            $this->entity = $entity;
        }

        return $this->entity;
    }

    public function setEntity(AssetAwareInterface $entity, string $column): self
    {
        $this->setEntityClass($entity::class);
        if (!$entity->getId()) {
            throw new \Exception('ID of given entity to pointer is invalid (missing ID)', 500);
        }
        $this->setEntityId($entity->getId());
        $this->setEntityColumn($column);
        $this->entity = $entity;

        return $this;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): self
    {
        if (!class_exists($entityClass)) {
            throw new \Exception(sprintf('Invalid entityClass given %s', $entityClass), 500);
        }

        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getEntityColumn(): ?string
    {
        return $this->entityColumn;
    }

    public function setEntityColumn(string $entityColumn): self
    {
        $this->entityColumn = $entityColumn;

        return $this;
    }

    /**
     * @return Collection<int, AssetThumbnailPointer>
     */
    public function getThumbnailPointers(): Collection
    {
        return $this->thumbnailPointers;
    }

    public function addThumbnail(AssetThumbnailPointer $thumbnail): self
    {
        if (!$this->thumbnailPointers->contains($thumbnail)) {
            $this->thumbnailPointers->add($thumbnail);
            $thumbnail->setAssetPointer($this);
        }

        return $this;
    }

    public function removeThumbnail(AssetThumbnailPointer $thumbnail): self
    {
        if ($this->thumbnailPointers->removeElement($thumbnail)) {
            // set the owning side to null (unless already changed)
            if ($thumbnail->getAssetPointer() === $this) {
                $thumbnail->setAssetPointer(null);
            }
        }

        return $this;
    }
}
