<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 */
class ValidateUpdateEntity extends EventAbstract
{
    /**
     * @param T                        $entity
     * @param array<int|string, mixed> $payload
     */
    public function __construct(
        public object $entity,
        public array $payload,
        public bool $isValid = false,
    ) {
        parent::__construct();
    }
}
