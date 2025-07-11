<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Trait\NormalizerHelperTrait;
use Dullahan\Entity\Presentation\Event\Transport\RegisterEntityNormalizer;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Model\Context;

/**
 * @phpstan-import-type SerializedEntity from \Dullahan\Entity\Port\Application\EntitySerializerInterface
 * @phpstan-import-type EntityDefinition from \Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface
 */
class SerializeEntityFunctor
{
    use NormalizerHelperTrait;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param EntityDefinition $definition
     *
     * @return SerializedEntity
     */
    public function __invoke(object $entity, array $definition, Context $context): array
    {
        $eventRegister = new RegisterEntityNormalizer($entity);
        $eventRegister->context->setContext($context->getContext());

        // @TODO maybe additionally allow for settings default normalizers in configuration? There may be cases when it
        //      depends on the entity but this seems non-intuitive to only allow adding them by event
        $normalizers = $this->eventDispatcher->dispatch($eventRegister)->getSortedNormalizers();

        $serialized = [];
        foreach ($definition as $fieldName => $field) {
            $serialized[$fieldName] = $this->tryReadField($entity, $fieldName);
            foreach ($normalizers as $normalizer) {
                if ($normalizer->canNormalize($fieldName, $serialized[$fieldName], $field, $entity, $context)) {
                    $serialized[$fieldName] = $normalizer->normalize($fieldName, $serialized[$fieldName], $field, $entity, $context);
                }
            }

            if (!array_key_exists($fieldName, $serialized)) {
                $serialized[$fieldName] = null;
            }
        }

        return $serialized;
    }
}
