<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Flush;

use Dullahan\Main\Model\Context;

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
