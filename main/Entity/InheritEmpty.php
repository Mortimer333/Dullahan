<?php

namespace Dullahan\Main\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Main\Repository\InheritEmptyRepository;

#[ORM\Entity(repositoryClass: InheritEmptyRepository::class)]
#[ORM\Index(name: 'entity_class_idx', fields: ['entityClass'])]
#[ORM\Index(name: 'entity_id_idx', fields: ['entityId'])]
#[ORM\Index(name: 'entity_field_idx', fields: ['entityField'])]
class InheritEmpty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $entityClass = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\Column(length: 255)]
    private ?string $entityField = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityClass(): ?string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): self
    {
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

    public function getEntityField(): ?string
    {
        return $this->entityField;
    }

    public function setEntityField(string $entityField): self
    {
        $this->entityField = $entityField;

        return $this;
    }
}
