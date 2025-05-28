<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Clear;

use Dullahan\Asset\Domain\Context;

final class ClearAssetEvent
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
