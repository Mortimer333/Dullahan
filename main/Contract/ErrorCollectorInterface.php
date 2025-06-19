<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract;

/**
 * Error collectors holds errors that happened during the request as a way to define more than one exception in a
 * decentralized manner. Errors can be added from different places and replace each other.
 *
 * Collected error are always an array of strings (string[]) with keys defining a path to field/domain to which they
 * matter. Path length is >= 0 so errors can be global, not assigned to any specific field.
 *
 * Example:
 * $errors = [
 *     "username" => ["Username is already taken"],
 * ];
 *
 * Or:
 *
 * $errors = ["Your body is invalid", "This EP does not accept PUT method"];
 *
 * This type is recursive (which PHPStan does not support), so to make it a little better, then just array<mixed> we
 * define at least 3 first level before going to mixed solution.
 *
 * @phpstan-type Error array<string, array<string, array<string, mixed>|array<string>>|array<string>>|array<string>
 */
interface ErrorCollectorInterface
{
    /**
     * @param array<string>|null $path Defines the path to the error, e.g. ["user", "details", "name"] which will result
     *                                 in array: ["user" => ["details" => ["name" => ["Invalid username"]]]
     */
    public function addError(string $error, ?array $path = null): void;

    /**
     * @param Error $errors
     */
    public function setErrors(array $errors): void;

    /**
     * @return Error
     */
    public function getErrors(): array;

    public function hasErrors(): bool;

    public function clearErrors(): void;
}
