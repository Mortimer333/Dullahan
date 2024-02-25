<?php

declare(strict_types=1);

namespace Dullahan\Doctrine\Automate;

abstract class MonsterPackAutomate
{
    public static function currentTime(): \DateTimeInterface
    {
        return new \DateTime();
    }
}
