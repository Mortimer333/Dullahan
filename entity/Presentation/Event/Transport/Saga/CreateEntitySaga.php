<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport\Saga;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\SagaAbstract;

class CreateEntitySaga extends SagaAbstract
{
    /**
     * @param array<mixed> $payload
     */
    public function __construct(
        public string $mapping,
        public string $path,
        public array $payload,
        RequestInterface $request,
    ) {
        parent::__construct($request);
    }
}
