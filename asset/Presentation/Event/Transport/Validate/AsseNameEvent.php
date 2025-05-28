<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Validate;

use Dullahan\Asset\Domain\Context;

final class AsseNameEvent
{
    public function __construct(
        protected string $name,
        protected Context $context,
        protected bool $valid = false,
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }
}
