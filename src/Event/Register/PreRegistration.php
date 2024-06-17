<?php

declare(strict_types=1);

namespace Dullahan\Event\Register;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class PreRegistration extends Event
{
    public const NAME = 'dullahan.pre_registration';

    public function __construct(
        protected Request $request,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
