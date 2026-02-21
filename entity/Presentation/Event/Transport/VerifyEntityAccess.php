<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

/**
 * @template T of object
 */
class VerifyEntityAccess extends EventAbstract
{
    public bool $isValid = false;

    /**
     * @param class-string<T> $className
     */
    public function __construct(
        public readonly string $className,
        public readonly ?User $user,
        public readonly string $type,
    ) {
        parent::__construct();
    }
}
