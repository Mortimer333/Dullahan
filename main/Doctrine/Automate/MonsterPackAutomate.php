<?php

declare(strict_types=1);

namespace Dullahan\Main\Doctrine\Automate;

/**
 * @TODO what? what is this?
 */
abstract class MonsterPackAutomate
{
    public static function currentTime(): \DateTimeInterface
    {
        return new \DateTime();
    }
}
