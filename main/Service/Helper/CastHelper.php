<?php

declare(strict_types=1);

namespace Dullahan\Main\Service\Helper;

abstract class CastHelper
{
    public static function cast(mixed $value, string $type): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        return match ($type) {
            'integer' => (int) $value,
            'string' => (string) $value,
            'float' => (float) $value,
            default => $value,
        };
    }
}
