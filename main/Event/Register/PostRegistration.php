<?php

declare(strict_types=1);

namespace Dullahan\Main\Event\Register;

use Dullahan\Main\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class PostRegistration extends Event
{
    public const NAME = 'dullahan.post_registration';

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
