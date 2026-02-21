<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

/**
 * @template T of object
 */
class VerifyEntityOwnership extends EventAbstract
{
    public bool $isValid = false;

    /**
     * @param T $entity
     */
    public function __construct(
        public readonly object $entity,
        public readonly ?User $user,
    ) {
        parent::__construct();
    }
}
