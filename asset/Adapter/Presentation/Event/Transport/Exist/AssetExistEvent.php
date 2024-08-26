<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\Transport\Exist;

use Dullahan\Asset\Domain\Context;

final class AssetExistEvent
{
    public function __construct(
        protected string $path,
        protected Context $context,
        protected bool $exists = false,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function setExists(bool $exists): void
    {
        $this->exists = $exists;
    }
}
