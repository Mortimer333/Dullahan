<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

class CacheRemoveEntity extends EventAbstract
{
    public function __construct(
        public object $entity,
    ) {
        parent::__construct();
    }
}
