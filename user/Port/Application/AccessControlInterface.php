<?php

declare(strict_types=1);

namespace Dullahan\User\Port\Application;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;

interface AccessControlInterface
{
    /**
     * @throws AccessDeniedHttpException
     */
    public function validateCSRFAttack(object $controller, RequestInterface $request): void;

    /**
     * @throws AccessDeniedHttpException
     */
    public function validateTokenExists(object $controller, RequestInterface $request): void;

    /**
     * @throws AccessDeniedHttpException
     */
    public function validateRoutesAccess(object $controller, RequestInterface $request): void;
}
