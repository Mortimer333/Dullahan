<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Presentation\Event\Transport\Validate\AsseNameEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final readonly class ValidNameListener
{
    #[AsEventListener(event: AsseNameEvent::class)]
    public function postCreateAsset(AsseNameEvent $event): void
    {
        $name = $event->getName();
        if (!str_contains($name, DIRECTORY_SEPARATOR)) {
            $event->setValid(true);
        }
    }
}
