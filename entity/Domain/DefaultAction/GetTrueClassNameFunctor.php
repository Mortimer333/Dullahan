<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Dullahan\Entity\Presentation\Event\Transport\GetEntityTrueClass;

/**
 * @TODO this is a doctrine fix functor - should be on the symfony adapter side
 */
class GetTrueClassNameFunctor
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    /**
     * @return class-string
     */
    public function __invoke(GetEntityTrueClass $event): string
    {
        if (!(class_implements($event->entity)[Proxy::class] ?? false)) {
            return $event->entity::class;
        }

        /* @var Proxy $entity */
        return $this->em->getClassMetadata($event->entity::class)->rootEntityName;
    }
}
