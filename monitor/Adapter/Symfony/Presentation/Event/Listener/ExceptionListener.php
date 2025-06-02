<?php

declare(strict_types=1);

namespace Dullahan\Monitor\Adapter\Symfony\Presentation\Event\Listener;

use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Monitor\Port\Domain\TraceServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        protected BinUtilService $baseUtilService,
        protected HttpUtilService $httpUtilService,
        protected TraceServiceInterface $traceService,
        protected ManagerRegistry $managerRegistry,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $this->httpUtilService->getProperResponseFromException($exception);
        $status = $this->httpUtilService->getStatusCode($exception);

        if ($status >= 500) {
            $this->traceService->create($exception, $event->getRequest(), $response);
        }
    }
}
