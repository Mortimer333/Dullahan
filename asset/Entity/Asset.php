<?php

namespace Dullahan\Asset\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Asset\Adapter\Infrastructure\Doctrine\Repository\AssetRepository;
use Dullahan\Asset\Application\Port\Infrastructure\AssetEntityInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetPointerInterface;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Entity\UserData;
use Dullahan\Main\Trait\UserDataRelationTrait;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[ORM\Index(name: 'path_search_idx', fields: ['directory', 'name', 'extension'])]
#[ORM\UniqueConstraint(name: 'full_path_unique_idx', fields: ['fullPath'])]
class Asset implements AssetEntityInterface
{
    use UserDataRelationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullPath = null;

    #[ORM\Column(length: 255)]
    private ?string $directory = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $weight = null;

    #[ORM\Column]
    private ?bool $hidden;

    /**
     * @var Collection<int, AssetPointerInterface>
     */
    #[ORM\OneToMany(mappedBy: 'asset', targetEntity: AssetPointer::class, orphanRemoval: true)]
    private Collection $pointers; // @phpstan-ignore-line

    #[ORM\ManyToOne(inversedBy: 'assets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserData $userData = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $modified = null;

    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->pointers = new ArrayCollection();
        $this->hidden = false;
    }

    public function getOwner(): ?User
    {
        return $this->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullPath(): string
    {
        return (string) $this->fullPath;
    }

    public function setFullPath(string $fullPath): self
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return Collection<int, AssetPointerInterface>
     */
    public function getPointers(): \IteratorAggregate&\Countable
    {
        return $this->pointers;
    }

    public function addPointer(AssetPointerInterface $pointer): self
    {
        if (!$this->pointers->contains($pointer)) {
            $this->pointers->add($pointer);
            $pointer->setAsset($this);
        }

        return $this;
    }

    public function removePointer(AssetPointerInterface $pointer): self
    {
        if ($this->pointers->removeElement($pointer)) {
            // set the owning side to null (unless already changed)
            if ($pointer->getAsset() === $this) {
                $pointer->setAsset(null);
            }
        }

        return $this;
    }

    public function getUserData(): ?UserData
    {
        return $this->userData;
    }

    public function setUserData(?UserData $userData): self
    {
        $this->userData = $userData;

        return $this;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): self
    {
        $this->modified = $modified;

        return $this;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function setDirectory(?string $directory): void
    {
        $this->directory = $directory;
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
