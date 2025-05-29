<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

use Dullahan\Main\Contract\RequestInterface;
use SplFileInfo;

/**
 * @TODO Are we sure about mutability of this class? I mean Symfony does it but shouldn't this be immutable? It is not
 *   like the request changes after receiving it, right?
 */
class Request implements RequestInterface
{
    /**
     * @param array<string, string|null|(string|null)[]> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $cookies
     * @param array<string, SplFileInfo> $files
     */
    public function __construct(
        private bool $isSecure,
        private string $host,
        private string $path,
        private string $method,
        private string $body = '',
        private array $headers = [],
        private array $query = [],
        private array $cookies = [],
        private array $files = [],
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function isSecure(): bool
    {
        return $this->isSecure;
    }

    public function getSchema(): string
    {
        return $this->isSecure ? 'https': 'http';
    }

    public function setIsSecure(bool $isSecure): static
    {
        $this->isSecure = $isSecure;

        return $this;
    }

    public function getPath(): string
    {
       return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $key, mixed $default = null): string|null
    {
        return $this->headers[$key] ?? $default;
    }

    public function setHeader(string $key, mixed $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function hasHeader(string $key): bool
    {
        return array_key_exists($this->headers, $key);
    }

    public function removeHeader(string $key): static
    {
        unset($this->headers[$key]);

        return $this;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getQueryParameter(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function setQueryParameter(string $key, mixed $value): static
    {
        $this->query[$key] = $value;

        return $this;
    }

    public function hasQueryParameter(string $key): bool
    {
        return array_key_exists($this->query, $key);
    }

    public function removeQueryParameter(string $key): static
    {
        unset($this->headers[$key]);

        return $this;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getCookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function setCookie(string $key, mixed $value): static
    {
        $this->cookies[$key] = $value;

        return $this;
    }

    public function hasCookie(string $key): bool
    {
        return array_key_exists($this->cookies, $key);
    }

    public function removeCookie(string $key): static
    {
        unset($this->headers[$key]);

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getFile(string $key): SplFileInfo|null
    {
        return $this->files[$key] ?? null;
    }

    public function setFile(string $key, $value): static
    {
        $this->files[$key] = $value;

        return $this;
    }

    public function hasFile(string $key): bool
    {
        return array_key_exists($this->files, $key);
    }

    public function removeFile(string $key): static
    {
        unset($this->files[$key]);

        return $this;
    }
}
