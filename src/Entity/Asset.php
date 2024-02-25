<?php

namespace Dullahan\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Repository\AssetRepository;
use Dullahan\Service\Util\BinUtilService;
use Dullahan\Service\Util\FileUtilService;
use Dullahan\Trait\UserDataRelationTrait;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'path_search_idx', fields: ['path', 'name', 'extension'])]
class Asset
{
    use UserDataRelationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $extension = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $weight = null;

    #[ORM\Column(length: 255)]
    private ?string $project = null;

    #[ORM\OneToMany(mappedBy: 'asset', targetEntity: AssetPointer::class, orphanRemoval: true)]
    private Collection $pointers;

    #[ORM\ManyToOne(inversedBy: 'assets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserData $userData = null;

    #[ORM\OneToMany(mappedBy: 'asset', targetEntity: Thumbnail::class, orphanRemoval: true)]
    private Collection $thumbnails;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $modified = null;

    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->pointers = new ArrayCollection();
        $this->thumbnails = new ArrayCollection();
    }

    #[ORM\PostRemove]
    public function remove(): void
    {
        FileUtilService::removeFEImages((string) $this->getProject(), $this->getProjectPath());
    }

    // TODO figure out proper path resolving for images
    public function getURL(): string
    {
        return rtrim(BinUtilService::projectToUrl((string) $this->getProject()), '/') . '/'
            . trim((string) $this->getPath(), '/') . '/' . $this->getName() . '.' . $this->getExtension()
            . '?v=' . $this->getModified()?->getTimestamp();
    }

    public function getFullPathWithoutName(): string
    {
        return rtrim(
            $_ENV['PATH_FRONT_END'] . '/' . $this->getProject() . '/' . trim((string) $this->getPath(), '/'),
            '/',
        );
    }

    public function getProjectPath(): string
    {
        return trim((string) $this->getPath(), '/') . '/' . $this->getName() . '.' . $this->getExtension();
    }

    public function getRelativePath(): string
    {
        return $this->getProject() . '/' . ltrim($this->getProjectPath(), '/');
    }

    public function getFullPath(): string
    {
        return $_ENV['PATH_FRONT_END'] . '/' . ltrim($this->getRelativePath(), '/');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

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

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getProject(): ?string
    {
        return $this->project;
    }

    public function setProject(string $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, AssetPointer>
     */
    public function getPointers(): Collection
    {
        return $this->pointers;
    }

    public function addPointer(AssetPointer $pointer): self
    {
        if (!$this->pointers->contains($pointer)) {
            $this->pointers->add($pointer);
            $pointer->setAsset($this);
        }

        return $this;
    }

    public function removePointer(AssetPointer $pointer): self
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

    /**
     * @return Collection<int, Thumbnail>
     */
    public function getThumbnails(): Collection
    {
        return $this->thumbnails;
    }

    public function addThumbnail(Thumbnail $thumbnail): self
    {
        if (!$this->thumbnails->contains($thumbnail)) {
            $this->thumbnails->add($thumbnail);
            $thumbnail->setAsset($this);
        }

        return $this;
    }

    public function removeThumbnail(Thumbnail $thumbnail): self
    {
        if ($this->thumbnails->removeElement($thumbnail)) {
            // set the owning side to null (unless already changed)
            if ($thumbnail->getAsset() === $this) {
                $thumbnail->setAsset(null);
            }
        }

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
}
