<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Port\Domain\ManageableInterface;
use Dullahan\Entity\Port\Domain\OwnerlessManageableInterface;
use Dullahan\Entity\Port\Domain\TransferableOwnerManageableInterface;
use Dullahan\User\Domain\Entity\User;

class VerifyEntityOwnershipFunctor
{
    public function __invoke(object $entity, ?User $user): bool
    {
        if ($entity instanceof OwnerlessManageableInterface) {
            return true;
        }

        if (
            !$user
            || (
                !($entity instanceof ManageableInterface)
                && !($entity instanceof TransferableOwnerManageableInterface)
            )
        ) {
            return false;
        }

        return $entity->isOwner($user);
    }
}
