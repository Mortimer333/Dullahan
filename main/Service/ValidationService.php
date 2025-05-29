<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Trait\Validate\EntityValidationTrait;
use Dullahan\Main\Trait\Validate\SymfonyValidationHelperTrait;
use Dullahan\User\Port\Application\UserServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    use SymfonyValidationHelperTrait;
    use EntityValidationTrait;

    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService, // @TODO-Asset-Decouple
    ) {
    }
}
