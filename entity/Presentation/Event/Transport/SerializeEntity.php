<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 *
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class SerializeEntity extends EventAbstract
{
    /** @var SerializedEntity|null */
    public ?array $serialized = null;

    /**
     * @param T                          $entity
     * @param EntityDefinition           $definition
     * @param array<NormalizerInterface> $normalizers
     */
    public function __construct(
        public readonly object $entity,
        public array $definition,
        public readonly array $normalizers,
        public bool $inherit = true,
    ) {
        parent::__construct();
    }
}
