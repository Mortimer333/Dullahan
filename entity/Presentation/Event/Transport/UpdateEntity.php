<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 */
class UpdateEntity extends EventAbstract
{
    /**
     * @param T                        $entity
     * @param array<int|string, mixed> $payload
     */
    public function __construct(
        public object $entity,
        public array $payload,
        public bool $flush = true
    ) {
        parent::__construct();
    }
}
