<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Trait;

trait NormalizerHelperTrait
{
    protected function tryReadField(object $entity, string $fieldName): mixed
    {
        try {
            return $entity->$fieldName;
        } catch (\Throwable) {  // @phpstan-ignore-line
            // Do nothing
        }

        $method = 'get' . ucfirst($fieldName);  // @phpstan-ignore-line
        if (!is_callable([$entity, $method])) {
            return null;
        }

        return $entity->$method();
    }
}
