<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

use Dullahan\Main\Contract\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestSymfonyImp implements RequestInterface
{
    public function __construct(
        protected Request $request,
    ) {
    }

    public function getPath(): string
    {
        return $this->request->getPathInfo();
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function getBody(): string
    {
        return (string) $this->request->getContent();
    }

    public function getHeaders(): array
    {
        return $this->request->headers->all();
    }

    public function getHeader(string $key, mixed $default = null): string|null
    {
        return $this->request->headers->get($key, $default);
    }

    public function getQueryParameter(string $key, mixed $default = null): mixed
    {
        return $this->request->query->get($key, $default);
    }

    public function getQuery(): array
    {
        return $this->request->query->all();
    }
}
