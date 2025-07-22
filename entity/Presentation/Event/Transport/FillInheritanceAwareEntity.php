<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Main\Model\EventAbstract;

class FillInheritanceAwareEntity extends EventAbstract
{
    public function __construct(
        public readonly InheritanceAwareInterface $entity,
    ) {
        parent::__construct();
    }
}
