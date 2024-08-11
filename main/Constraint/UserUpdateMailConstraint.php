<?php

declare(strict_types=1);

namespace Dullahan\Main\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateMailConstraint
{
    public static function get(): Assert\Collection
    {
        return new Assert\Collection([
            'email' => RegistrationConstraint::getMail(),
            'password' => RegistrationConstraint::getPassword(),
        ]);
    }
}
