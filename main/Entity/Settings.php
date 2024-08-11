<?php

namespace Dullahan\Main\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Main\Repository\SettingsRepository;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    public const SYSTEM = [
        'maxTakenSpacePrecent' => 'maxTakenSpacePrecent',
        'takenSpacePrecent' => 'takenSpacePrecent',
    ];
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    /** @var array<string, mixed> */
    #[ORM\Column]
    private array $data = [];

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
