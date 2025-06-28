<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Normalizers;

use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * @param \DateTime|\DateTimeImmutable $value
     */
    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): array|string|int|float|bool|\ArrayObject|null {
        return $value->getTimestamp();
    }

    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool {
        return $value instanceof \DateTime || $value instanceof \DateTimeImmutable;
    }
}
