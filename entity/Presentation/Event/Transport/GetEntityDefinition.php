<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 *
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class GetEntityDefinition extends EventAbstract
{
    /** @var EntityDefinition|null */
    public ?array $definition = null;

    /**
     * @param T $entity
     */
    public function __construct(
        public object $entity,
    ) {
        parent::__construct();
    }
}
