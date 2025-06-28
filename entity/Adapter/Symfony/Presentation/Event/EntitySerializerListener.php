<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Event;

use Dullahan\Entity\Adapter\Symfony\Domain\PrepareEntityRequestedDataFunctor;
use Dullahan\Entity\Domain\DefaultAction\RegisterEntityNormalizersFunctor;
use Dullahan\Entity\Domain\DefaultAction\SerializeEntityFunctor;
use Dullahan\Entity\Presentation\Event\Transport\RegisterEntityNormalizer;
use Dullahan\Entity\Presentation\Event\Transport\SerializeEntity;
use Dullahan\Entity\Presentation\Event\Transport\StripSerializedEntity;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class EntitySerializerListener
{
    public function __construct(
        protected SerializeEntityFunctor $serializeEntity,
        protected PrepareEntityRequestedDataFunctor $prepareEntityRequestedData,
        protected RegisterEntityNormalizersFunctor $registerEntityNormalizers,
    ) {
    }

    #[AsEventListener(event: SerializeEntity::class)]
    public function onSerializeEntity(SerializeEntity $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $event->serialized = ($this->serializeEntity)(
            $event->entity,
            $event->definition,
            $event->normalizers,
            $event->context,
        );
    }

    #[AsEventListener(event: StripSerializedEntity::class)]
    public function onStripSerializedEntity(StripSerializedEntity $event): void
    {
        if ($event->wasDefaultPrevented() || !$event->dataSet) {
            return;
        }

        $event->serialized = ($this->prepareEntityRequestedData)(
            $event->serialized,
            $event->dataSet,
            (bool) $event->context->getProperty('inherit', false),
        );
    }

    #[AsEventListener(event: RegisterEntityNormalizer::class)]
    public function onRegisterEntityNormalizer(RegisterEntityNormalizer $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        ($this->registerEntityNormalizers)($event);
    }
}
