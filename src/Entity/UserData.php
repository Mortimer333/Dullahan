<?php

declare(strict_types=1);

namespace Dullahan\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Repository\UserDataRepository;
use Dullahan\Attribute\Field;

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

    #[ORM\OneToMany(mappedBy: 'userData', targetEntity: Asset::class, orphanRemoval: true)]
    private Collection $assets;

    #[Field, ORM\Column(length: 255)]
    private ?string $publicId = null;

    #[ORM\Column(options: ['default' => 10 ** 7])] // Default 10Mb
    private ?int $fileLimitBytes = null;

    public function __construct()
    {
        $this->assets = new ArrayCollection();
        $this->fileLimitBytes = 10 ** 7;
    }

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

    /**
     * @return Collection<int, Asset>
     */
    public function getAssets(): Collection
    {
        return $this->assets;
    }

    public function addAsset(Asset $asset): self
    {
        if (!$this->assets->contains($asset)) {
            $this->assets->add($asset);
            $asset->setUserData($this);
        }

        return $this;
    }

    public function removeAsset(Asset $asset): self
    {
        if ($this->assets->removeElement($asset)) {
            // set the owning side to null (unless already changed)
            if ($asset->getUserData() === $this) {
                $asset->setUserData(null);
            }
        }

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

    public function getFileLimitBytes(): ?int
    {
        return $this->fileLimitBytes;
    }

    public function setFileLimitBytes(int $fileLimitBytes): self
    {
        $this->fileLimitBytes = $fileLimitBytes;

        return $this;
    }
}
