<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Presentation\Event\EventListener\Asset;

use Dullahan\Asset\Domain\Normalizers\AssetPointerNormalizer;
use Dullahan\Entity\Presentation\Event\Transport\RegisterEntityNormalizer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntityNormalizerListener
{
    public function __construct(
        protected AssetPointerNormalizer $assetPointerNormalizer,
    ) {
    }

    #[AsEventListener(event: RegisterEntityNormalizer::class, priority: 10)]
    public function onRegisterEntityNormalizer(RegisterEntityNormalizer $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->register($this->assetPointerNormalizer, 25);
    }
}
