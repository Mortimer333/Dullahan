<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Event\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Main\Service\Util\FileUtilService;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Presentation\Event\Transport\SerializeUser;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class SerializationListener
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[AsEventListener(event: SerializeUser::class)]
    public function onSerializeUser(SerializeUser $event): void
    {
        if ($event->wasDefaultPrevented()) {
            return;
        }

        $user = $event->user;
        /** @var UserData $data */
        $data = $user->getData();
        $currentTakenSpace = $this->em->getRepository(Asset::class)->getTakenSpace($data);
        $event->setSerialized(
            [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'data' => [
                    'id' => $data->getId(),
                    'name' => $data->getName(),
                ],
                'storage' => [
                    'readable' => [
                        'limit' => FileUtilService::humanFilesize((int) $data->getFileLimitBytes()),
                        'taken' => FileUtilService::humanFilesize($currentTakenSpace),
                    ],
                    'limit' => (int) $data->getFileLimitBytes(),
                    'taken' => $currentTakenSpace,
                ],
            ]
        );
    }
}
