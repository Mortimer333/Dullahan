<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\ValueObject;

class UserBaseline
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        #[\SensitiveParameter] public readonly string $password,
        #[\SensitiveParameter] public readonly string $passwordRepeat,
    ) {
    }
}
