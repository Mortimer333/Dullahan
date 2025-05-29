<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transform;

use Dullahan\Main\Contract\RequestInterface;

class PostValidationRegistration
{
    public function __construct(
        protected RequestInterface $request,
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
