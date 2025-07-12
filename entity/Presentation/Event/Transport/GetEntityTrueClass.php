<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

class GetEntityTrueClass extends EventAbstract
{
    /** @var class-string|null */
    public ?string $className = null;

    public function __construct(
        public object $entity,
    ) {
        parent::__construct();
    }
}
