<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Presentation\Event\Transport\SerializeUser;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class SerializationListener
{
    #[AsEventListener(event: SerializeUser::class)]
    public function onSerializeUser(SerializeUser $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $user = $event->user;
        /** @var UserData $data */
        $data = $user->getData();
        $event->setSerialized(
            [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'data' => [
                    'id' => $data->getId(),
                    'name' => $data->getName(),
                ],
            ]
        );
    }
}
