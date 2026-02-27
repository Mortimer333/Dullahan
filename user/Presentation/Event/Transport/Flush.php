<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

final class Flush extends EventAbstract
{
    public const FLUSH_PURPOSE = 'flush_purpose';
    public const REGISTER = 'register';
    public const ENABLE_PASSWORD_RESET = 'enable_password_reset';

    public function __construct(
        public readonly User $user,
        Context $context = new Context(),
    ) {
        parent::__construct($context);

        if (!$context->hasProperty(self::FLUSH_PURPOSE)) {
            throw new \InvalidArgumentException(
                sprintf('Flush even is required to have %s parameter', self::FLUSH_PURPOSE),
                500,
            );
        }
    }
}
