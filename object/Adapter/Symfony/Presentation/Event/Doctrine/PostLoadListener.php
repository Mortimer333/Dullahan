<?php

declare(strict_types=1);

namespace Dullahan\Object\Adapter\Symfony\Presentation\Event\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Dullahan\Object\Domain\Contract\EntityManagerInjectionInterface;
use Dullahan\Object\Domain\Contract\InheritanceAwareInterface;
use Dullahan\Object\Domain\Mapper\EntityInheritanceMapper;
use Dullahan\Object\Port\Domain\EntityServiceInterface;

#[AsDoctrineListener(event: Events::postLoad)]
class PostLoadListener
{
    public function __construct(
        protected EntityServiceInterface $entityUtilService,
    ) {
    }

    public function postLoad(PostLoadEventArgs $event): void
    {
        $this->postInheritanceAwareLoad($event);
        $this->postEntityInject($event);
    }

    /**
     * @TODO this approach violates so many conventions, get reed of it
     */
    protected function postEntityInject(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityManagerInjectionInterface) {
            return;
        }

        $entity->setEntityManager($event->getObjectManager());
    }

    protected function postInheritanceAwareLoad(PostLoadEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof InheritanceAwareInterface) {
            return;
        }

        EntityInheritanceMapper::addInheritedParent($entity);
    }
}
