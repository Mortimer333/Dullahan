<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

class SerializeUser extends EventAbstract
{
    /**
     * @param array<mixed> $serialized
     */
    public function __construct(
        public readonly User $user,
        protected array $serialized = [],
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    /**
     * @return mixed[]
     */
    public function getSerialized(): array
    {
        return $this->serialized;
    }

    /**
     * @param array<mixed> $serialized
     */
    public function setSerialized(array $serialized): void
    {
        $this->serialized = $serialized;
    }
}
