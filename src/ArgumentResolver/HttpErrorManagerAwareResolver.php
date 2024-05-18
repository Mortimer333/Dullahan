<?php

declare(strict_types=1);

namespace Dullahan\ArgumentResolver;

use Dullahan\Service\ValidationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Overrides RequestPayloadValueResolver to save errors into HttpUtil
 */
readonly class HttpErrorManagerAwareResolver implements ValueResolverInterface, EventSubscriberInterface
{
    public function __construct(
        private RequestPayloadValueResolver $resolver,
        protected ValidationService $validationService,
    ) {
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        return $this->resolver->resolve($request, $argument);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', 256],
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        try {
            $this->resolver->onKernelControllerArguments($event);
        } catch (HttpException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                $this->validationService->addViolations($previous->getViolations());
            }

            throw $e;
        }
    }
}
