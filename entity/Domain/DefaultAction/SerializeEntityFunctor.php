<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Trait\NormalizerHelperTrait;
use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\Context;

/**
 * @template T of object
 *
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class SerializeEntityFunctor
{
    use NormalizerHelperTrait;

    /**
     * @param T                     $entity
     * @param EntityDefinition      $definition
     * @param NormalizerInterface[] $normalizers
     *
     * @return SerializedEntity
     */
    public function __invoke(object $entity, array $definition, array $normalizers, Context $context): array
    {
        $serialized = [];
        foreach ($definition as $fieldName => $field) {
            foreach ($normalizers as $normalizer) {
                $value = array_key_exists($fieldName, $serialized)
                    ? $serialized[$fieldName]
                    : $this->tryReadField($entity, $fieldName)
                ;
                if ($normalizer->canNormalize($fieldName, $value, $field, $entity, $context)) {
                    $serialized[$fieldName] = $normalizer->normalize($fieldName, $value, $field, $entity, $context);
                }
            }
        }

        return $serialized;
    }
}
