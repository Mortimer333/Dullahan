<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Event\Transform;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\User\Domain\Entity\User;

class PostLogin
{
    public function __construct(
        protected RequestInterface $request,
        protected User $user,
    ) {
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
