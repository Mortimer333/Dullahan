<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Event\Transport\Flush;

use Dullahan\Asset\Domain\Context;

final class FlushAssetEvent
{
    public function __construct(
        protected Context $context,
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
