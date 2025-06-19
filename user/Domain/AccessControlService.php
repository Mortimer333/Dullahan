<?php

declare(strict_types=1);

namespace Dullahan\User\Domain;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Contract\RequestInterface;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Dullahan\User\Port\Application\AccessControlInterface;
use Dullahan\User\Port\Domain\AuthorizationCheckerInterface;
use Dullahan\User\Presentation\Event\Transport\GetCSRF;

// @TODO move this to Event - handling should be done on an event
class AccessControlService implements AccessControlInterface
{
    public function __construct(
        protected string $secret,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function validateTokenCredibility(RequestInterface $request, array $tokenPayload): void
    {
        if (in_array($request->getMethod(), ['HEAD', 'OPTIONS'])) {
            return;
        }

        $csrfToken = $this->getCsrfToken($request);
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

    private function getCsrfToken(RequestInterface $request): ?string
    {
        $event = $this->eventDispatcher->dispatch(new GetCSRF($request));

        return $event->getCsrf();
    }
}
