<?php

namespace Dullahan\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Repository\AssetThumbnailPointerRepository;

#[ORM\Entity(repositoryClass: AssetThumbnailPointerRepository::class)]
class AssetThumbnailPointer
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'thumbnailPointers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AssetPointer $assetPointer = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'assetPointers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Thumbnail $thumbnail = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    public function getAssetPointer(): ?AssetPointer
    {
        return $this->assetPointer;
    }

    public function setAssetPointer(?AssetPointer $assetPointer): self
    {
        $this->assetPointer = $assetPointer;

        return $this;
    }

    public function getThumbnail(): ?Thumbnail
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?Thumbnail $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
