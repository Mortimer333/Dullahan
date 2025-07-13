<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Port\Domain\EntityValidationInterface;
use Dullahan\Entity\Presentation\Event\Transport\ValidateUpdateEntity;

class ValidateEntityUpdateFunctor
{
    public function __construct(
        protected EntityValidationInterface $entityValidation,
    ) {
    }

    public function __invoke(ValidateUpdateEntity $event): bool
    {
        return $this->entityValidation->isUpdatePayloadValid($event->entity, $event->payload);
    }
}
