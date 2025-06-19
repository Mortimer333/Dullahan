<?php

namespace Dullahan\User\Adapter\Symfony\Domain;

use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Port\Application\AccessControlInterface;
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
        protected HttpUtilService $httpUtilService,
        protected BinUtilService $baseUtilService,
        protected RequestFactory $requestFactory,
        protected AccessControlInterface $accessControl,
    ) {
    }

    /**
     * Skip authenticator when login route is accesses to give way for the default one.
     */
    public function supports(Request $request): ?bool
    {
        return 'api_user_login' !== $request->attributes->get('_route');
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

        // @TODO this should be a event
        $this->accessControl->validateTokenCredibility(
            $this->requestFactory->symfonyToDullahanRequest($request),
            $payload,
        );

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
        $exception = new AuthenticationException('Wrong password or username', 401, $exception);
        $this->baseUtilService->saveLastErrorTrace($exception);

        return $this->httpUtilService->getProperResponseFromException($exception);
    }
}
