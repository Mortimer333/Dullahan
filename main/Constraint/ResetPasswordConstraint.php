<?php

declare(strict_types=1);

namespace Dullahan\Main\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordConstraint
{
    public static function get(): Assert\Collection
    {
        return new Assert\Collection([
            'password' => RegistrationConstraint::getPassword(),
            'passwordRepeat' => RegistrationConstraint::getPasswordRepeat(),
        ]);
    }
}
