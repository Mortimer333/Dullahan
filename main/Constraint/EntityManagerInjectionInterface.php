<?php

declare(strict_types=1);

namespace Dullahan\Main\Constraint;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerInjectionInterface
{
    public function setEntityManager(EntityManagerInterface $em): self;
}
