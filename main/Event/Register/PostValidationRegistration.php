<?php

declare(strict_types=1);

namespace Dullahan\Main\Event\Register;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class PostValidationRegistration extends Event
{
    public const NAME = 'dullahan.post_registration';

    public function __construct(
        protected Request $request,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
