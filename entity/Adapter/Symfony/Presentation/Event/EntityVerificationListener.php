<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Domain\DefaultAction\VerifyEntityAccessibilityFunctor;
use Dullahan\Entity\Domain\DefaultAction\VerifyEntityOwnershipFunctor;
use Dullahan\Entity\Presentation\Event\Transport\VerifyEntityAccess;
use Dullahan\Entity\Presentation\Event\Transport\VerifyEntityOwnership;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @template T of object
 */
class EntityVerificationListener
{
    public function __construct(
        protected VerifyEntityAccessibilityFunctor $verifyEntityAccessibility,
        protected VerifyEntityOwnershipFunctor $verifyEntityOwnershipFunctor,
    ) {
    }

    /**
     * @param VerifyEntityAccess<T> $event
     */
    #[AsEventListener(event: VerifyEntityAccess::class)]
    public function onVerifyEntityAccess(VerifyEntityAccess $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->isValid = ($this->verifyEntityAccessibility)($event->className);
    }

    /**
     * @param VerifyEntityOwnership<T> $event
     */
    #[AsEventListener(event: VerifyEntityOwnership::class)]
    public function onVerifyEntityOwnership(VerifyEntityOwnership $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->isValid = ($this->verifyEntityOwnershipFunctor)($event->entity, $event->user);
    }
}
