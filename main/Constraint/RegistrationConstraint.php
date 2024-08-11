<?php

declare(strict_types=1);

namespace Dullahan\Main\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationConstraint
{
    public static function get(): Assert\Collection
    {
        return new Assert\Collection([
            'email' => self::getMail(),
            'username' => self::getUsername(),
            'password' => self::getPassword(),
            'passwordRepeat' => self::getPasswordRepeat(),
        ]);
    }

    /**
     * @return array<Constraint>
     */
    public static function getPasswordRepeat(string $password = 'repeated password'): array
    {
        return [
            new Assert\NotBlank(['message' => 'Missing ' . $password]),
            new Assert\Type([
                'type' => 'string',
                'message' => ucfirst($password) . ' must be a string',
            ]),
            new Assert\Length([
                'max' => 255,
                'maxMessage' => 'Your ' . $password . ' cannot exceed 255 characters',
            ]),
        ];
    }

    /**
     * @return array<Constraint>
     */
    public static function getPassword(string $password = 'password'): array
    {
        return [
            new Assert\NotBlank(['message' => 'Missing ' . $password]),
            new Assert\Type([
                'type' => 'string',
                'message' => ucfirst($password) . ' must be a string',
            ]),
            new Assert\Length([
                'max' => 255,
                'maxMessage' => 'Your ' . $password . ' cannot exceed 255 characters',
            ]),
        ];
    }

    /**
     * @return array<Constraint>
     */
    public static function getUsername(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Missing username']),
            new Assert\Type([
                'type' => 'string',
                'message' => 'Name of the user must be a string',
            ]),
            new Assert\Length([
                'min' => 3,
                'max' => 255,
                'maxMessage' => 'Your name cannot exceed 255 characters',
                'minMessage' => 'Your name cannot be shorter then 3 characters',
            ]),
        ];
    }

    /**
     * @return array<Constraint>
     */
    public static function getMail(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Missing email']),
            new Assert\Type([
                'type' => 'string',
                'message' => 'Email of the user must be a string',
            ]),
            new Assert\Email([
                'message' => 'Your email is invalid',
            ]),
            new Assert\Length([
                'max' => 255,
                'maxMessage' => 'Your email cannot exceed 255 characters',
            ]),
        ];
    }
}
