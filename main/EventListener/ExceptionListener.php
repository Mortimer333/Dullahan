<?php

declare(strict_types=1);

namespace Dullahan\Main\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        protected BinUtilService $baseUtilService,
        protected HttpUtilService $httpUtilService,
        protected ManagerRegistry $managerRegistry,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 1],
            LoginFailureEvent::class => ['loginFailure', 1],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $this->httpUtilService->getProperResponseFromException($exception);

        if (!$this->baseUtilService->isProduction()) {
            $this->baseUtilService->saveLastErrorTrace($exception, $event->getRequest());
        }

        $event->setResponse($response);
    }

    public function loginFailure(LoginFailureEvent $event): void
    {
        if (!$this->baseUtilService->isProduction()) {
            $this->baseUtilService->saveLastErrorTrace($event->getException());
        }

        $exception = new \Exception('Invalid credentials', 401);
        $event->setResponse(
            $this->httpUtilService->getProperResponseFromException($exception)
        );
    }
}
