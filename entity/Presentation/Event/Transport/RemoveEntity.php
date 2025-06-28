<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

class RemoveEntity extends EventAbstract
{
    public function __construct(
        public object $entity,
        public bool $flush = true
    ) {
        parent::__construct();
    }
}
