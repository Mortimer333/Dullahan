<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

class Context
{
    public const TYPE = 'type';

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private array $context = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function hasProperty(string $name): bool
    {
        return (bool) ($this->context[$name] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->context;
    }

    public function getProperty(string $name, mixed $default = null): mixed
    {
        return $this->context[$name] ?? $default;
    }

    public function setProperty(string $name, mixed $value): static
    {
        $this->context[$name] = $value;

        return $this;
    }

    public function removeProperty(string $name): static
    {
        unset($this->context[$name]);

        return $this;
    }
}
