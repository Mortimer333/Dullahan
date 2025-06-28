<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 */
class GetEntityRepository extends EventAbstract
{
    /** @var EntityRepositoryInterface<T>|null */
    public ?EntityRepositoryInterface $repository = null;

    /**
     * @param class-string<T> $class
     */
    public function __construct(
        public readonly string $class,
    ) {
        parent::__construct();
    }
}
