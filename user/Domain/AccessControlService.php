<?php

declare(strict_types=1);

namespace Dullahan\User\Domain;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Dullahan\User\Port\Application\AccessControlInterface;
use Dullahan\User\Port\Domain\AuthorizationCheckerInterface;

// @TODO move this to Event - handling should be done on an event
class AccessControlService implements AccessControlInterface
{
    public function __construct(
        protected string $secret,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function validateTokenCredibility(RequestInterface $request, array $tokenPayload): void
    {
        if (in_array($request->getMethod(), ['HEAD', 'OPTIONS'])) {
            return;
        }

        // @TODO make it a parameter
        $csrfToken = $request->getHeader('x-csrf-token');
        if (!$csrfToken) {
            throw new AccessDeniedHttpException('CSRF token missing');
        }

        $csrfTokenChunks = explode('.', $csrfToken);
        if (2 != count($csrfTokenChunks)) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
        [$hmac, $random] = $csrfTokenChunks;

        if (!isset($tokenPayload['session'])) {
            throw new AccessDeniedHttpException('Missing session token');
        }

        $expectedHmac = explode('.', $this->generateCSRFToken($tokenPayload['session'], $random))[0];

        if (!hash_equals($expectedHmac, $hmac)) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }

    public function generateCSRFToken(string $session, ?string $random = null): string
    {
        $random ??= bin2hex(random_bytes(64));
        $message = strlen($session) . '!' . $session . '!' . strlen($random) . '!' . $random;
        $hmac = hash_hmac('sha256', $message, $this->secret) . '.' . $random;

        return $hmac;
    }
}
