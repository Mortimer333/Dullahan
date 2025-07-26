<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Infrastructure\EntityRepositoryInterface;
use Dullahan\Main\Model\EventAbstract;

class GetEntity extends EventAbstract
{
    public ?IdentityAwareInterface $entity = null;

    /**
     * @param EntityRepositoryInterface<object> $repository
     * @param class-string                      $class
     */
    public function __construct(
        public readonly string $class,
        public readonly int $id,
        public readonly EntityRepositoryInterface $repository,
    ) {
        parent::__construct();
    }
}
