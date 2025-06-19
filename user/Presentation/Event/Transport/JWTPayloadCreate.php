<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\User\Domain\Entity\User;

/**
 * @phpstan-type TokenPayload array{
 *      user_id: int|null,
 *      user: string|null,
 *  }
 */
class JWTPayloadCreate
{
    /**
     * @param TokenPayload $payload
     */
    public function __construct(
        protected array $payload,
        protected User $user,
    ) {
    }

    /**
     * @return TokenPayload
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param TokenPayload $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
