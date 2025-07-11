<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\Response\Response;

abstract class SagaAbstract extends EventAbstract
{
    protected ?Response $response = null;

    public function __construct(
        public readonly RequestInterface $request,
    ) {
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function hasResponse(): bool
    {
        return !is_null($this->response);
    }
}
