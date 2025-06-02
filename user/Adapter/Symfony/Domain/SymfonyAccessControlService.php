<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Domain;

use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\User\Adapter\Symfony\Application\AccessControlService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\RequestStack;

class SymfonyAccessControlService extends AccessControlService
{
    public function __construct(
        protected Security $security,
        protected BinUtilService $baseUtilService,
        protected RequestStack $requestStack,
        protected RequestFactory $requestFactory,
    ) {
    }

    public function validate(object $controller, SymfonyRequest $symfonyRequest): void
    {
        // @TODO arbitrary rule, should be defined by the user not framework
        if (!preg_match('/^\/(_\/(user|login))/', $symfonyRequest->getPathInfo())) {
            return;
        }

        $request = $this->requestFactory->symfonyToDullahanRequest($symfonyRequest);
        !$this->isSwaggerRequestOnDev() && $this->validateCSRFAttack($controller, $request);
        $this->validateTokenExists($controller, $request);
        $this->validateRoutesAccess($controller, $request);
    }

    protected function isSwaggerRequestOnDev(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->baseUtilService->isDev() && $request && $request->headers->get('X-Swagger');
    }
}
