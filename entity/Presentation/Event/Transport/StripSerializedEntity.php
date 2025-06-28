<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 *
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 */
class StripSerializedEntity extends EventAbstract
{
    /**
     * @param T                    $entity
     * @param array<string, mixed> $dataSet
     * @param SerializedEntity     $serialized
     */
    public function __construct(
        public object $entity,
        public array $serialized,
        public ?array $dataSet,
    ) {
        parent::__construct();
    }
}
