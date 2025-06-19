<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait\Validate;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait SymfonyValidationHelperTrait
{
    /**
     * @param array<mixed>                 $body
     * @param Constraint|array<Constraint> $constraint
     */
    public function validate(array $body, Constraint|array $constraint): void
    {
        $this->addViolations($this->validator->validate($body, $constraint));
    }

    public function addViolations(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->errorCollector->addError(
                (string) $violation->getMessage(),
                explode('][', ltrim(rtrim($violation->getPropertyPath(), ']'), '['))
            );
        }
    }
}
