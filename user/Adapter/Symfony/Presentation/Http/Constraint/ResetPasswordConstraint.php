<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Http\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordConstraint
{
    public static function get(): Assert\Collection
    {
        return new Assert\Collection([
            'forgotten' => new Assert\Collection([
                'password' => RegistrationConstraint::getPassword(),
                'passwordRepeat' => RegistrationConstraint::getPasswordRepeat(),
                'token' => [
                    new Assert\NotBlank(['message' => 'Cannot be empty']),
                    new Assert\Type([
                        'type' => 'string',
                        'message' => 'Token must be a string',
                    ]),
                ],
            ]),
        ]);
    }
}
