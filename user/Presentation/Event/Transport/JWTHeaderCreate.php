<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\User\Domain\Entity\User;

/**
 * @phpstan-type TokenHeader array{
 *      alg: string,
 *      jti: string,
 *      iss: string,
 *      aud: string,
 *      iat: int,
 *      nbf: int,
 *      exp: int,
 *  }&array<string, number|string>
 */
class JWTHeaderCreate
{
    /**
     * @param TokenHeader $header
     */
    public function __construct(
        protected array $header,
        protected User $user,
    ) {
    }

    /**
     * @return TokenHeader
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param TokenHeader $header
     */
    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
