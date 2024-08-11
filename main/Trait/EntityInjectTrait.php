<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait;

use Doctrine\ORM\EntityManagerInterface;

trait EntityInjectTrait // @phpstan-ignore-line
{
    protected EntityManagerInterface $em;

    public function setEntityManager(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }
}
