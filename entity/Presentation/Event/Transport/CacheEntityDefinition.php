<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 *
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class CacheEntityDefinition extends EventAbstract
{
    /**
     * @param T                $entity
     * @param EntityDefinition $definition
     */
    public function __construct(
        public readonly object $entity,
        public readonly array $definition,
    ) {
        parent::__construct();
    }
}
