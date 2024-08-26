<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\Transport\Create;

use Dullahan\Asset\Application\Port\Presentation\NewStructureInterface;
use Dullahan\Asset\Domain\Context;

final class PreCreateAssetEvent
{
    public function __construct(
        protected NewStructureInterface $file,
        protected Context $context,
    ) {
    }

    public function getFile(): NewStructureInterface
    {
        return $this->file;
    }

    public function setFile(NewStructureInterface $file): void
    {
        $this->file = $file;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
