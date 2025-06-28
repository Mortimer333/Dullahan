<?php

declare(strict_types=1);

namespace Dullahan\Main\Symfony;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        protected SymfonyEventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}
