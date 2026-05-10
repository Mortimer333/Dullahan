<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain\Normalizers;

use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Asset\Port\Presentation\AssetSerializeManagerInterface;
use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

class AssetPointerNormalizer implements NormalizerInterface
{
    public function __construct(
        private AssetSerializeManagerInterface $assetSerializeManager,
    ) {
    }

    public function normalize(
        string $fieldName,
        mixed $value,
        array $definition,
        object $entity,
        Context $context,
    ): mixed {
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

        return $this->assetSerializeManager->serializePointer($value);
    }
}
