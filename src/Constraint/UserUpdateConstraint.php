<?php

declare(strict_types=1);

namespace Dullahan\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateConstraint
{
    public static function get(): Assert\Collection
    {
        return new Assert\Collection([
            'username' => new Assert\Optional(RegistrationConstraint::getUsername()),
            'sendNewsletter' => new Assert\Optional([
                new Assert\NotNull(['message' => 'Missing Send Newsletter']),
                new Assert\Type([
                    'type' => 'boolean',
                    'message' => 'Send Newsletter must be a boolean value',
                ]),
            ]),
        ]);
    }
}
