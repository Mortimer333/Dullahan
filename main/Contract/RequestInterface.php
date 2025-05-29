<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

interface RequestInterface
{
    public function getHost(): string;

    public function setHost(string $host): static;

    public function isSecure(): bool;

    public function setIsSecure(bool $isSecure): static;

    public function getSchema(): string;

    public function getPath(): string;

    public function setPath(string $path): static;

    public function getMethod(): string;

    public function setMethod(string $method): static;

    public function getBody(): string;

    public function setBody(string $body): static;

    /**
     * @return array<string, string|(string|null)[]|null>
     */
    public function getHeaders(): array;

    public function getHeader(string $key, mixed $default = null): mixed;

    public function setHeader(string $key, mixed $value): static;

    public function hasHeader(string $key): bool;

    public function removeHeader(string $key): static;

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array;

    public function getQueryParameter(string $key, mixed $default = null): mixed;

    public function setQueryParameter(string $key, mixed $value): static;

    public function hasQueryParameter(string $key): bool;

    public function removeQueryParameter(string $key): static;

    /**
     * @return array<string, mixed>
     */
    public function getCookies(): array;

    public function getCookie(string $key, mixed $default = null): mixed;

    public function setCookie(string $key, mixed $value): static;

    public function hasCookie(string $key): bool;

    public function removeCookie(string $key): static;

    /**
     * @return array<string, \SplFileInfo>
     */
    public function getFiles(): array;

    public function getFile(string $key): ?\SplFileInfo;

    /**
     * @param \SplFileInfo $value
     */
    public function setFile(string $key, $value): static;

    public function hasFile(string $key): bool;

    public function removeFile(string $key): static;
}
