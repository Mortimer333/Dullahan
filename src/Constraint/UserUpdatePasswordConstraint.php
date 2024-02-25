<?php

declare(strict_types=1);

namespace Dullahan\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdatePasswordConstraint
{
    public static function get(): Assert\Collection
    {
        return new Assert\Collection([
            'oldPassword' => RegistrationConstraint::getPassword(),
            'newPassword' => RegistrationConstraint::getPassword(' new password'),
            'newPasswordRepeat' => RegistrationConstraint::getPassword('repeated new password'),
        ]);
    }
}
