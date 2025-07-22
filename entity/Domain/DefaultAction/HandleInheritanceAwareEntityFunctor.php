<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Entity\Domain\Mapper\EntityInheritanceMapper;
use Dullahan\Entity\Port\Domain\InheritanceAwareInterface;
use Dullahan\Entity\Presentation\Event\Transport\FillInheritanceAwareEntity;

class HandleInheritanceAwareEntityFunctor
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function __invoke(FillInheritanceAwareEntity $event): void
    {
        $entity = $event->entity;
        if (!$entity->getParent()) {
            $entity->setRelationPath(null);
        } else {
            $parentPath = $entity->getParent()->getRelationPath();
            if (is_null($parentPath)) {
                $parentPath = (string) $entity->getParent()->getId();
            } else {
                $parentPath .= ',' . $entity->getParent()->getId();
            }

            // Is being created
            if (!$entity->getId()) {
                $entity->setRelationPath($parentPath);

                return;
            }
        }

        if (!EntityInheritanceMapper::didParentChange($entity)) {
            return;
        }

        $parentPath = trim($entity->getRelationPath() . ',' . $entity->getId(), ',');
        $this->assignNewRelationPathToChildren($entity->getChildren()->toArray(), $parentPath);
    }

    /**
     * @param array<int, object> $children
     */
    protected function assignNewRelationPathToChildren(
        array $children,
        string $path,
    ): void {
        foreach ($children as $child) {
            if (!$child instanceof InheritanceAwareInterface) {
                continue;
            }

            $this->em->persist($child);
            $child->setRelationPath($path);
            $this->assignNewRelationPathToChildren(
                $child->getChildren()->toArray(),
                $path . ',' . $child->getId(),
            );
        }
    }
}
