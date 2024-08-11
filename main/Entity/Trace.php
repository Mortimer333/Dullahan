<?php

namespace Dullahan\Main\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dullahan\Main\Repository\TraceRepository;

#[ORM\Entity(repositoryClass: TraceRepository::class)]
class Trace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var array<string|int, mixed>|null
     */
    #[ORM\Column(nullable: true)]
    private ?array $payload = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $endpoint = null;

    #[ORM\Column(length: 255)]
    private string $ip;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column]
    private array $response = [];

    #[ORM\Column]
    private int $code;

    #[ORM\Column(nullable: true)]
    private ?int $userId = null;

    #[ORM\Column]
    private int $created;

    /**
     * @var array<array<string, mixed>>
     */
    #[ORM\Column]
    private array $trace = [];

    public function __construct()
    {
        $this->created = time();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array<string|int, mixed>|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param array<string|int, mixed>|null $payload
     */
    public function setPayload(?array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array<string, mixed> $response
     */
    public function setResponse(array $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function setCreated(int $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getTrace(): array
    {
        return $this->trace;
    }

    /**
     * @param array<array<string, mixed>> $trace
     */
    public function setTrace(array $trace): self
    {
        $this->trace = $trace;

        return $this;
    }
}
