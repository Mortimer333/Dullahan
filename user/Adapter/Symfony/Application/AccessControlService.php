<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Application;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Dullahan\User\Port\Application\AccessControlInterface;
use Dullahan\User\Port\Presentation\Http\DisableDoubleSubmitAuthenticationInterface;
use Dullahan\User\Port\Presentation\Http\DisableTokenAuthenticationInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @TODO This shouldn't be framework specific
 */
class AccessControlService implements AccessControlInterface
{
    public function __construct(
        protected Security $security,
        protected BinUtilService $binUtilService,
    ) {
    }

    public function validateRoutesAccess(object $controller, RequestInterface $request): void
    {
        // @TODO this will probably a config parameter - where to put all the user related (and protected) routes
        if (preg_match('/^\/(_\/user)/', $request->getPath())) {
            /** @var ?User $user */
            $user = $this->security->getUser();
            if (null === $user) {
                throw new AccessDeniedHttpException('You need to be logged in to access this resource');
            }

            if (!$this->security->isGranted('ROLE_USER')) {
                throw new AccessDeniedHttpException('You need to be a user in order to access this resource');
            }
        }
    }

    public function validateTokenExists(object $controller, RequestInterface $request): void
    {
        // Making sure that user will be verified by custom authorization
        if (
            !$controller instanceof DisableTokenAuthenticationInterface
            && !$request->hasHeader('authorization')
        ) {
            throw new AccessDeniedHttpException('Token is required to access this resource');
        }
    }

    public function validateCSRFAttack(object $controller, RequestInterface $request): void
    {
        $cookieToken = $request->getCookie('CSRF-Token');

        $res = (!$this->isSwaggerRequest() || !$this->baseUtilService->isDev())       // is not swagger request on dev
            && !$controller instanceof DisableDoubleSubmitAuthenticationInterface     // is double submit check control
            && !in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])           // is change data request
            && (                                                                      // doesn't have csrf token or
                !$request->hasHeader('x-csrf-token')                                  // tokens don't match
                || !isset($cookieToken)
                || $request->getHeader('x-csrf-token') !== $cookieToken
            )
        ;


        $file = fopen( '/var/www/html/Dullahan/var/test', 'w');
        fwrite($file, json_encode([
            $request->hasHeader('x-csrf-token'),
            !isset($cookieToken),
            $request->getHeader('x-csrf-token'),
            $request->getHeader('x-csrf-token') !== $cookieToken
        ], JSON_PRETTY_PRINT));
        fclose($file);

        if ($res) {
            throw new AccessDeniedHttpException('CSRF attack');
        }
    }

}
