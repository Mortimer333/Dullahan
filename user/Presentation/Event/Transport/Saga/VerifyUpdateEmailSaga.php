<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\Saga;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\SagaAbstract;

class VerifyUpdateEmailSaga extends SagaAbstract
{
    public function __construct(
        RequestInterface $request,
        readonly public int $userId,
        #[\SensitiveParameter] readonly public string $token,
    ) {
        parent::__construct($request);
    }
}
