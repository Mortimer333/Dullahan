<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Main\Model\EventAbstract;

class PersistUpdatedEntity extends EventAbstract
{
    /**
     * @param array<int|string, mixed> $payload
     */
    public function __construct(
        public readonly IdentityAwareInterface $entity,
        public readonly array $payload,
        public bool $flush = true
    ) {
        parent::__construct();
    }
}
