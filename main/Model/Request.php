<?php

declare(strict_types=1);

namespace Dullahan\Main\Model;

use Dullahan\Main\Contract\RequestInterface;

/**
 * @TODO Are we sure about mutability of this class? I mean Symfony does it but shouldn't this be immutable? It is not
 *   like the request changes after receiving it, right?
 */
class Request implements RequestInterface
{
    /**
     * @param array<string, string|(string|null)[]|null> $headers
     * @param array<string, mixed>                       $query
     * @param array<string, mixed>                       $cookies
     * @param array<string, \SplFileInfo>                $files
     * @param array<string, mixed>                       $attributes
     * @param array<mixed>                               $bodyParameters
     */
    public function __construct(
        private bool $isSecure,
        private string $host,
        private string $path,
        private string $method,
        private object $original,
        private string $body = '',
        private array $headers = [],
        private array $query = [],
        private array $cookies = [],
        private array $files = [],
        private array $attributes = [],
        private array $bodyParameters = [],
    ) {
        $flatHeaders = [];
        foreach ($headers as $key => $header) {
            $flatHeaders[$key] = is_array($header) && 1 === count($header) ? $header[0] : $header;
        }

        $this->headers = $flatHeaders;
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
        return $this->isSecure ? 'https' : 'http';
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

    public function getHeader(string $key, mixed $default = null): mixed
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
        return array_key_exists($key, $this->headers);
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
        return array_key_exists($key, $this->query);
    }

    public function removeQueryParameter(string $key): static
    {
        unset($this->query[$key]);

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
        return array_key_exists($key, $this->cookies);
    }

    public function removeCookie(string $key): static
    {
        unset($this->cookies[$key]);

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getFile(string $key): ?\SplFileInfo
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
        return array_key_exists($key, $this->files);
    }

    public function removeFile(string $key): static
    {
        unset($this->files[$key]);

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function removeAttribute(string $key): static
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function getOriginal(): object
    {
        return $this->original;
    }

    public function getBodyParameters(): array
    {
        return $this->bodyParameters;
    }

    public function getBodyParameter(string $key, mixed $default = null): mixed
    {
        return $this->bodyParameters[$key] ?? $default;
    }

    public function setBodyParameter(string $key, mixed $value): static
    {
        $this->bodyParameters[$key] = $value;

        return $this;
    }

    public function hasBodyParameter(string $key): bool
    {
        return array_key_exists($key, $this->bodyParameters);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getQueryParameter($key)
            ?? $this->getBodyParameter($key)
            ?? $this->getCookie($key)
            ?? $this->getAttribute($key)
            ?? $this->getHeader($key)
            ?? $default
        ;
    }
}
