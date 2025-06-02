<?php

declare(strict_types=1);

namespace Dullahan\Object\Domain\Contract;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerInjectionInterface
{
    public function setEntityManager(EntityManagerInterface $em): self;
}
