<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Entity\Domain\Attribute\Field;
use Dullahan\User\Adapter\Symfony\Infrastructure\Repository\UserDataRepository;

/**
 * Public user information.
 */
#[ORM\Entity(repositoryClass: UserDataRepository::class)]
class UserData
{
    #[Field, ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[Field, ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'data', cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $deleted = null;

    #[Field, ORM\Column(length: 255, nullable: true)]
    private ?string $oldName = null;

    #[Field, ORM\Column(length: 255)]
    private ?string $publicId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(?int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getOldName(): ?string
    {
        return $this->oldName;
    }

    public function setOldName(?string $oldName): self
    {
        $this->oldName = $oldName;

        return $this;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId): self
    {
        $this->publicId = $publicId;

        return $this;
    }
}
