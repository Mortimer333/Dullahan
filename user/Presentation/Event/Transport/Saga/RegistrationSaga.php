<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Saga;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\SagaAbstract;

class RegistrationSaga extends SagaAbstract
{
    public function __construct(
        RequestInterface $request,
    ) {
        parent::__construct($request);
    }
}
