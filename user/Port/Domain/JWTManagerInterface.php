<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Domain;

use Dullahan\User\Domain\Entity\User;

interface JWTManagerInterface
{
    public function createToken(User $user): string;

    /**
     * @return array<mixed>
     */
    public function validateAndGetPayload(?string $token): array;
}
