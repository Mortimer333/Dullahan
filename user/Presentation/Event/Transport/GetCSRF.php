<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\EventAbstract;

class GetCSRF extends EventAbstract
{
    protected ?string $csrf = null;

    public function __construct(
        protected RequestInterface $request,
    ) {
        parent::__construct();
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getCsrf(): ?string
    {
        return $this->csrf;
    }

    public function setCsrf(?string $csrf): void
    {
        $this->csrf = $csrf;
    }
}
