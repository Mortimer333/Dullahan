<?php

declare(strict_types=1);

namespace Dullahan\Object\Presentation\Event\Transport;

class OwnershipCheck
{
    public function __construct(
        protected PreCreate|PreUpdate|PreRemove $event,
    ) {
    }

    public function getEvent(): PreCreate|PreUpdate|PreRemove
    {
        return $this->event;
    }
}
