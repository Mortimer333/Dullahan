<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

class CacheRemoveRelated extends EventAbstract
{
    /**
     * @param array<mixed> $definition
     */
    public function __construct(
        public object $entity,
        public array $definition,
    ) {
        parent::__construct();
    }
}
