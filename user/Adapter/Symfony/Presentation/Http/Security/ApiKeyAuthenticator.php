<?php

namespace Dullahan\User\Adapter\Symfony\Presentation\Http\Security;

use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Port\Domain\JWTManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        protected JWTManagerInterface $jwtService,
        protected HttpUtilService     $httpUtilService,
        protected BinUtilService      $baseUtilService,
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('Authorization');

        if (null === $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No token provided');
        }

        if (str_starts_with($apiToken, 'Bearer ')) {
            $apiToken = mb_substr($apiToken, 7);
        }

        $payload = $this->jwtService->validateAndGetPayload($apiToken);
        $request->attributes->set('_token_payload', $payload);

        return new SelfValidatingPassport(new UserBadge($payload['user']));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $exception = new \Exception('Wrong password or username', 401);
        $this->baseUtilService->saveLastErrorTrace($exception);

        return $this->httpUtilService->getProperResponseFromException($exception);
    }
}
