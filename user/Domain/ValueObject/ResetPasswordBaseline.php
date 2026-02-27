<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\ValueObject;

class ResetPasswordBaseline
{
    public function __construct(
        public readonly string $password,
        public readonly string $token,
    ) {
    }
}
