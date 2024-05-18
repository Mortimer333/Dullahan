<?php

declare(strict_types=1);

namespace Dullahan\Event;

use Dullahan\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class PostLogin
{
    public const NAME = 'dullahan.post_login';

    public function __construct(
        protected Request $request,
        protected User $user,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
