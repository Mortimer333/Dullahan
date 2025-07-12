<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Main\Model\EventAbstract;

class CreateEntity extends EventAbstract
{
    public ?IdentityAwareInterface $entity = null;

    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     */
    public function __construct(
        public readonly string $class,
        public array $payload,
        public bool $flush = true
    ) {
        parent::__construct();
    }
}
