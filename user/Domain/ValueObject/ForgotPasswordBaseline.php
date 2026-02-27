<?php

declare(strict_types=1);

namespace Dullahan\User\Domain\ValueObject;

class ForgotPasswordBaseline
{
    public function __construct(
        public readonly string $email,
    ) {
    }
}
