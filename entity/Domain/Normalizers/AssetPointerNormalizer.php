<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Normalizers;

use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Port\Presentation\AssetSerializerInterface;
use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

class AssetPointerNormalizer implements NormalizerInterface
{
    public function __construct(
        protected AssetSerializerInterface $assetSerializer,
    ) {
    }

    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): array|string|int|float|bool|\ArrayObject|null {
        return $this->getAsset($value);
    }

    public function canNormalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): bool {
        return AssetPointer::class === $definition['type'];
    }

    /**
     * @return array<string, int|string|array<string, string|null>|null>|null
     */
    protected function getAsset(mixed $value): ?array
    {
        if (!($value instanceof AssetPointer) || !$value->getAsset()) {
            return null;
        }

        return $this->assetSerializer->serializePointer($value);
    }
}
