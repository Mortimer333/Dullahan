<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Port\Domain\EntityValidationInterface;
use Dullahan\Entity\Presentation\Event\Transport\ValidateCreateEntity;

class ValidateEntityCreationFunctor
{
    public function __construct(
        protected EntityValidationInterface $entityValidation,
    ) {
    }

    public function __invoke(ValidateCreateEntity $event): bool
    {
        return $this->entityValidation->isCreatePayloadValid($event->class, $event->payload);
    }
}
