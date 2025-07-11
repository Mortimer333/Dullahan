<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

class CacheEntity extends EventAbstract
{
    public function __construct(
        public string $key,
        public string $toCache,
        public int|\DateInterval|null $expiry,
        public readonly string $case,
    ) {
        parent::__construct();
    }
}
