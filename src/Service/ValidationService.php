<?php

declare(strict_types=1);

namespace Dullahan\Service;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Service\User\UserValidateService;
use Dullahan\Service\Util\HttpUtilService;
use Dullahan\Trait\Validate\AssetValidationTrait;
use Dullahan\Trait\Validate\EntityValidationTrait;
use Dullahan\Trait\Validate\RegistrationValidationTrait;
use Dullahan\Trait\Validate\UserValidationTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    use RegistrationValidationTrait;
    use UserValidationTrait;
    use EntityValidationTrait;
    use AssetValidationTrait;

    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $em,
        protected UserValidateService $userValidateService,
        protected UserService $userService,
    ) {
    }

    /**
     * @param array<mixed>                 $body
     * @param Constraint|array<Constraint> $constraint
     */
    public function validate(array $body, Constraint|array $constraint): void
    {
        $violations = $this->validator->validate($body, $constraint);
        foreach ($violations as $violation) {
            $this->httpUtilService->addError(
                (string) $violation->getMessage(),
                explode('][', substr($violation->getPropertyPath(), 1, -1))
            );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) - the main idea is to have a lot of different outcomes/flexibility
     */
    public function validatePasswordStrength(
        string $password,
        bool $upper = true,
        bool $lower = true,
        bool $number = true,
        bool $special = true,
        int $length = 8,
    ): bool {
        $valid = true;
        if (mb_strlen($password) < $length) {
            $valid = false;
            $this->httpUtilService->addError("Password is too short, it is required to have $length characters");
        }

        if ($upper && !preg_match('@[A-Z]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have uppercase characters');
        }

        if ($lower && !preg_match('@[a-z]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have lowercase characters');
        }

        if ($number && !preg_match('@[0-9]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have numeric characters');
        }

        if ($special && !preg_match('@[^\w]@', $password)) {
            $valid = false;
            $this->httpUtilService->addError('Password is required to have special characters');
        }

        return $valid;
    }
}
