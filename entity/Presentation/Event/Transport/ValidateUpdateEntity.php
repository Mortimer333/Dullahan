<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Main\Model\EventAbstract;

class ValidateUpdateEntity extends EventAbstract
{
    /**
     * @param array<int|string, mixed> $payload
     */
    public function __construct(
        public IdentityAwareInterface $entity,
        public array $payload,
        public bool $isValid = false,
    ) {
        parent::__construct();
    }
}
