<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

class CacheRemoveEntityId extends EventAbstract
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public readonly string $class,
        public readonly int $id,
    ) {
        parent::__construct();
    }
}
