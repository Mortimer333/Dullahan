<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Trait\Validate\SymfonyValidationHelperTrait;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    use SymfonyValidationHelperTrait;

    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected ValidatorInterface $validator,
    ) {
    }
}
