<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\Main\Contract\RequestInterface;

class PreRegistration
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
