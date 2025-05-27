<?php

namespace Dullahan\Thumbnail\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Asset\Application\Port\Presentation\AssetPointerInterface;
use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Thumbnail\Adapter\Infrastructure\Database\Repository\AssetThumbnailPointerRepository;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailPointerInterface;

#[ORM\Entity(repositoryClass: AssetThumbnailPointerRepository::class)]
class AssetThumbnailPointer implements ThumbnailPointerInterface
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

    public function getAssetPointer(): ?AssetPointerInterface
    {
        return $this->assetPointer;
    }

    public function setAssetPointer(?AssetPointerInterface $assetPointer): self
    {
        $this->assetPointer = $assetPointer; // @phpstan-ignore-line

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
