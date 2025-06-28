<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 *
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 */
class CacheSerializedEntity extends EventAbstract
{
    /**
     * @param T                $entity
     * @param SerializedEntity $serialized
     */
    public function __construct(
        public readonly object $entity,
        public readonly array $serialized,
        public readonly bool $inherit,
    ) {
        parent::__construct();
    }
}
