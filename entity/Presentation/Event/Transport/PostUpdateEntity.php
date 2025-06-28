<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 */
class PostUpdateEntity extends EventAbstract
{
    /**
     * @param T $entity
     */
    public function __construct(
        public object $entity,
    ) {
        parent::__construct();
    }
}
