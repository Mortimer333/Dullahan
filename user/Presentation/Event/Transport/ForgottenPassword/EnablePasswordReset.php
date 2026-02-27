<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transport\ForgottenPassword;

use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;
use Dullahan\User\Domain\Entity\User;

final class EnablePasswordReset extends EventAbstract
{
    public function __construct(
        private User $user,
        Context $context = new Context(),
    ) {
        parent::__construct($context);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
