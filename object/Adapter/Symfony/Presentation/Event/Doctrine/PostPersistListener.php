<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Presentation\Event\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Object\Domain\Contract\InheritanceAwareInterface;
use Dullahan\Object\Domain\Mapper\EntityInheritanceMapper;
use Dullahan\Object\Port\Domain\EntityServiceInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500)]
class PostPersistListener
{
    public function __construct(
        protected EntityServiceInterface $entityUtilService,
    ) {
    }

    public function postPersist(PostPersistEventArgs $event): void
    {
        $this->updateCurrentInheritedParents($event);
    }

    protected function updateCurrentInheritedParents(PostPersistEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        $inherited = EntityInheritanceMapper::getCurrentInheritedParents();
        $inherited[$entity::class][(int) $entity->getId()] = $entity->getParent()?->getId();
        EntityInheritanceMapper::setCurrentInheritedParents($inherited);
    }
}
