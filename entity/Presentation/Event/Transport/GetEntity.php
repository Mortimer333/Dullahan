<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 */
class GetEntity extends EventAbstract
{
    public ?IdentityAwareInterface $entity = null;

    /**
     * @param class-string<T>              $class
     * @param EntityRepositoryInterface<T> $repository
     */
    public function __construct(
        public readonly string $class,
        public readonly int $id,
        public readonly EntityRepositoryInterface $repository,
    ) {
        parent::__construct();
    }
}
