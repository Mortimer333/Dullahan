<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class SerializeEntity extends EventAbstract
{
    /** @var SerializedEntity|null */
    public ?array $serialized = null;

    /**
     * @param EntityDefinition $definition
     */
    public function __construct(
        public readonly object $entity,
        public array $definition,
        public bool $inherit = true,
    ) {
        parent::__construct();
    }
}
