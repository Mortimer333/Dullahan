<?php

declare(strict_types=1);

namespace Dullahan\Main\Event\Entity;

use Symfony\Contracts\EventDispatcher\Event;

class OwnershipCheck extends Event
{
    public const NAME = 'dullahan.ownership_check';

    public function __construct(
        protected PreCreate|PreUpdate|PreRemove $event,
    ) {
    }

    public function getEvent(): PreCreate|PreUpdate|PreRemove
    {
        return $this->event;
    }
}
