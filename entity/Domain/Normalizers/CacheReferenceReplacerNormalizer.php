<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Normalizers;

use Dullahan\Entity\Port\Domain\EntityCacheServiceInterface;
use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

class CacheReferenceReplacerNormalizer implements NormalizerInterface
{
    public function __construct(
        protected EntityCacheServiceInterface $entityCacheService,
    ) {
    }

    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): array|string|int|float|bool|\ArrayObject|null {
        return [
            '__cached' => $this->entityCacheService->getEntitySerializedCacheKey(
                $value,
                $context->getProperty('inherit'),
            ),
        ];
    }

    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool {
        return is_object($value);
    }
}
