<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Application;

use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Domain\AuthorizationCheckerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AuthorizationCheckerService implements AuthorizationCheckerInterface
{
    public function __construct(
        protected Security $security,
        protected RoleHierarchyInterface $roleHierarchy,
    ) {
    }

    public function canAccess(mixed $path, ?object $user): bool
    {
        // @TODO this will probably a config parameter - where to put all the user related (and protected) routes
        if (preg_match('/^\/(_\/user)/', $path)) {
            if (!($user instanceof User)) {
                return false;
            }

            return in_array('ROLE_USER', $this->roleHierarchy->getReachableRoleNames($user->getRoles()));
        }

        return true;
    }
}
