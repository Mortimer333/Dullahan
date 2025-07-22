<?php

declare(strict_types=1);

namespace Dullahan\Entity\Port\Domain;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @deprecated
 */
interface EntityManagerInjectionInterface
{
    public function setEntityManager(EntityManagerInterface $em): self;
}
