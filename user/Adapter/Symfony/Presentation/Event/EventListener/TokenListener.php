<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\User\Presentation\Event\Transport\GetCSRF;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class TokenListener
{
    #[AsEventListener(event: GetCSRF::class)]
    public function onGetCSRF(GetCSRF $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->setCsrf($event->getRequest()->getHeader('x-csrf-token'));
    }
}
