<?php

namespace Dullahan\Thumbnail\Adapter\Infrastructure\Database\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DullahanMainContract\AssetManager\AssetInterface;
use DullahanMainEntity\Asset;
use DullahanMainEntity\AssetThumbnailPointer;
use Dullahan\Thumbnail\Adapter\Infrastructure\Database\Repository\ThumbnailRepository;
use Thumbnail\Application\Port\Presentation\ThumbnailInterface;

#[ORM\Entity(repositoryClass: ThumbnailRepository::class)]
// #[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'duplicate_find_idx', fields: ['asset', 'settings'])]
class Thumbnail implements ThumbnailInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'thumbnails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $weight = null;

    #[ORM\Column]
    private ?string $settings = null;

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

    public function getEntity(): ThumbnailInterface
    {
        return $this;
    }

    public function getFile()
    {
        throw new \Exception('Not implemented');
    }

    //    #[ORM\PostRemove]
    //    public function remove(): void
    //    {
    //        if (!$this->getAsset()) {
    //            return;
    //        }
    //
    //        FileUtilService::removeFEImages($this->getProjectPath());
    //    }

    //    public function getURL(): string
    //    {
    //        return rtrim(BinUtilService::projectToUrl((string) $this->getAsset()?->getProject()), '/')
    //            . '/' . trim((string) $this->getAsset()?->getPath(), '/')
    //            . '/' . $this->getName() . '.' . $this->getAsset()?->getExtension()
    //            . '?v=' . $this->getAsset()?->getModified()?->getTimestamp();
    //    }

    //    public function getProjectPath(): string
    //    {
    //        return trim((string) $this->getAsset()?->getPath(), '/')
    //            . '/' . $this->getName() . '.' . $this->getAsset()?->getExtension();
    //    }

    //    public function getRelativePath(): string
    //    {
    //        return ltrim($this->getProjectPath(), '/');
    //    }
    //
    //    public function getFullPath(): string
    //    {
    //        return $_ENV['PATH_FRONT_END'] . '/' . ltrim($this->getRelativePath(), '/');
    //    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAsset(): ?AssetInterface
    {
        return $this->asset;
    }

    public function setAsset(?AssetInterface $asset): self
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
    public function getAssetPointers(): \Iterator
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
}
