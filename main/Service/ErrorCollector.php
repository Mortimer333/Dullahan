<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Dullahan\Main\Contract\ErrorCollectorInterface;

/**
 * @phpstan-import-type Error from \Dullahan\Main\Contract\ErrorCollectorInterface
 */
final class ErrorCollector implements ErrorCollectorInterface
{
    /** @var Error $errors */
    private array $errors = [];
    /** @var array<string> $prefix */
    private array $prefix = [];

    public function addError(string $error, ?array $path = null): void
    {
        if (count($this->prefix) > 0 && count($path ?? []) > 0) {
            $path = [...$this->prefix, ...$path];
        }

        if (null === $path) {
            $this->errors[] = $error;

            return;
        }

        self::createErrorPath($error, $path, $this->errors);
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * @param array<string> $path
     * @param Error         $errors
     */
    private function createErrorPath(string $error, array $path, array &$errors, int $caret = 0): void
    {
        if (!isset($path[$caret])) {
            $errors[] = $error;

            return;
        }

        if (!isset($errors[$path[$caret]])) {
            $errors[$path[$caret]] = [];
        }

        if (is_string($errors[$path[$caret]])) {
            return;
        }

        self::createErrorPath($error, $path, $errors[$path[$caret]], $caret + 1);
    }

    public function setPrefixPath(array $path): void
    {
        $this->prefix = $path;
    }
}
