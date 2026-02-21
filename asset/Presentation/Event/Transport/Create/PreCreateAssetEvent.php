<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Create;

use Dullahan\Asset\Port\Presentation\NewStructureInterface;
use Dullahan\Main\Model\Context;

final class PreCreateAssetEvent
{
    public function __construct(
        private NewStructureInterface $file,
        private Context $context,
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
