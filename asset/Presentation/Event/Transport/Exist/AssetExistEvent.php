<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Exist;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;

final class AssetExistEvent extends EventAbstract
{
    public function __construct(
        protected string $path,
        Context $context,
        protected ?bool $exists = null,
    ) {
        parent::__construct($context);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function exists(): ?bool
    {
        return $this->exists;
    }

    public function setExists(?bool $exists): void
    {
        $this->exists = $exists;
    }
}
