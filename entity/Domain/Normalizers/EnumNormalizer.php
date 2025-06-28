<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Normalizers;

use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

class EnumNormalizer implements NormalizerInterface
{
    /**
     * @param \BackedEnum $value
     */
    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): array|string|int|float|bool|\ArrayObject|null {
        return $value->value;
    }

    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool {
        return is_object($value) && enum_exists($value::class) && $value instanceof \BackedEnum;
    }
}
