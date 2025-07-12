<?php

declare(strict_types=1);

namespace Dullahan\Main\Symfony;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Contract\ValidationServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SymfonyConstraintValidationService implements ValidationServiceInterface
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected ErrorCollectorInterface $errorCollector,
    ) {
    }

    /**
     * @param array<mixed>                 $body
     * @param Constraint|array<Constraint> $constraint
     */
    public function validate(array $body, mixed $constraint): bool
    {
        if (!$this->hasValidConstraints($constraint)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s::validate only accepts %s violations',
                    static::class,
                    Constraint::class,
                ),
                500,
            );
        }

        $violations = $this->validator->validate($body, $constraint);
        $this->addViolations($violations);

        return 0 === count($violations);
    }

    public function addViolations(mixed $violations): void
    {
        if (!$violations instanceof ConstraintViolationListInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s::addViolations only accepts %s violations',
                    static::class,
                    ConstraintViolationListInterface::class,
                ),
                500,
            );
        }

        foreach ($violations as $violation) {
            $this->errorCollector->addError(
                (string) $violation->getMessage(),
                explode('][', ltrim(rtrim($violation->getPropertyPath(), ']'), '['))
            );
        }
    }

    protected function hasValidConstraints(mixed $constraint): bool
    {
        if ($constraint instanceof Constraint) {
            return true;
        }

        if (is_array($constraint)) {
            foreach ($constraint as $item) {
                if (!$item instanceof Constraint) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
