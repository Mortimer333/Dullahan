<?php

namespace Dullahan\Asset\Domain\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Asset\Adapter\Symfony\Infrastructure\Doctrine\Repository\AssetPointerRepository;
use Dullahan\Asset\Port\Infrastructure\AssetAwareInterface;
use Dullahan\Asset\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Port\Infrastructure\PointerInterface;
use Dullahan\Asset\Port\Presentation\AssetPointerInterface;

#[ORM\Entity(repositoryClass: AssetPointerRepository::class)]
class AssetPointer implements PointerInterface, AssetPointerInterface
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

    public function getEntity(): ?AssetAwareInterface
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

    public function getAssetId(): ?int
    {
        return $this->asset?->getId();
    }

    public function getAsset(): ?AssetEntityInterface
    {
        return $this->asset;
    }

    public function setAsset(?AssetEntityInterface $asset): self
    {
        /** @var Asset $asset2 */
        $asset2 = $asset;

        $this->asset = $asset2;

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
}
