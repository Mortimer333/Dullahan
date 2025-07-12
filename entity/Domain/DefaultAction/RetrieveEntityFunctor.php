<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Exception\InvalidEntityException;
use Dullahan\Entity\Port\Domain\IdentityAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\GetEntity;

/**
 * @template T of object
 */
class RetrieveEntityFunctor
{
    /**
     * @param GetEntity<T> $event
     */
    public function __invoke(GetEntity $event): ?IdentityAwareInterface
    {
        if (!class_exists($event->class)) {
            return null;
        }

        $entity = $event->repository->find($event->id);

        if (!$entity instanceof IdentityAwareInterface) {
            throw new InvalidEntityException(
                sprintf(
                    'Requested entity is not implementing %s, did you require not manageable entity?',
                    IdentityAwareInterface::class,
                ),
            );
        }

        return $entity;
    }
}
