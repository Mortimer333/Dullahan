<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Random\RandomException;

interface AccessControlInterface
{
    /**
     * @param array{
     *     session?: string
     * } $tokenPayload
     *
     * @throws AccessDeniedHttpException
     */
    public function validateTokenCredibility(RequestInterface $request, array $tokenPayload): void;

    /**
     * @throws RandomException
     * @throws AccessDeniedHttpException
     */
    public function generateCSRFToken(string $session, ?string $random = null): string;
}
