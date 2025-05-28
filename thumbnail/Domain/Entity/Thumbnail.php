<?php

namespace Dullahan\Thumbnail\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Thumbnail\Adapter\Symfony\Infrastructure\Database\Repository\ThumbnailRepository;
use Dullahan\Thumbnail\Port\Presentation\ThumbnailEntityInterface;

#[ORM\Entity(repositoryClass: ThumbnailRepository::class)]
#[ORM\Index(name: 'duplicate_find_idx', fields: ['settings', 'asset'])]
class Thumbnail implements ThumbnailEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $weight = null;

    #[ORM\Column]
    private ?string $settings = null;

    #[ORM\Column]
    private ?string $path = null;

    /**
     * @var Collection<int, AssetThumbnailPointer>
     */
    #[ORM\OneToMany(
        mappedBy: 'thumbnail',
        targetEntity: AssetThumbnailPointer::class,
        orphanRemoval: true,
        cascade: ['persist'],
    )]
    private Collection $assetPointers;

    public function __construct()
    {
        $this->assetPointers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function setSettings(string $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return Collection<int, AssetThumbnailPointer>
     */
    public function getAssetPointers(): \Traversable&\Countable
    {
        return $this->assetPointers;
    }

    public function addAssetPointer(AssetThumbnailPointer $assetPointer): self
    {
        if (!$this->assetPointers->contains($assetPointer)) {
            $this->assetPointers->add($assetPointer);
            $assetPointer->setThumbnail($this);
        }

        return $this;
    }

    public function removeAssetPointer(AssetThumbnailPointer $assetPointer): self
    {
        if ($this->removeElement($assetPointer)) {
            // set the owning side to null (unless already changed)
            if ($assetPointer->getThumbnail() === $this) {
                $assetPointer->setThumbnail(null);
            }
        }

        return $this;
    }

    /**
     * For some reason doctrine removeElement sometimes doesn't see AssetThumbnailPointers as the same objects and
     * doesn't remove them from Collection.
     */
    protected function removeElement(AssetThumbnailPointer $element): bool
    {
        if ($this->getAssetPointers()->removeElement($element)) {
            return true;
        }

        foreach ($this->getAssetPointers() as $i => $assetPointer) {
            if (
                $assetPointer->getThumbnail()?->getId() === $element->getThumbnail()?->getId()
                && $assetPointer->getAssetPointer()?->getId() === $element->getAssetPointer()?->getId()
                && $assetPointer->getCode() === $element->getCode()
            ) {
                return (bool) $this->getAssetPointers()->remove($i);
            }
        }

        return false;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }
}
