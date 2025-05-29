<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Dullahan\Main\Entity\Trace;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Domain\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @TODO a semi-good start for monitoring module
 */
class TraceService
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected HttpUtilService $httpUtilService,
        protected BinUtilService $binUtilService,
        protected Security $security,
        protected ManagerRegistry $managerRegistry,
    ) {
    }

    public function create(\Throwable $e, ?Request $request = null, ?Response $response = null): ?Trace
    {
        try {
            if (!$this->em->isOpen()) {
                return null;
            }

            $this->em->clear(); // Clear entity to avoid persistence errors
            if (!$response) {
                $response = $this->httpUtilService->getProperResponseFromException($e);
            }

            $status = $this->httpUtilService->getStatusCode($e);
            $trace = $this->generateTrace($status, $response, $e);

            if ($request) {
                $this->saveRequest($trace, $request);
            }

            $user = $this->security->getUser();
            if ($user && $user instanceof User) {
                $trace->setUserId($user->getId());
            }

            if (!$this->em->isOpen()) {
                $this->managerRegistry->resetManager(); // Have to reset entity on exception
            }

            $this->em->persist($trace);
            $this->em->flush();

            return $trace;
        } catch (\Throwable) {
            throw $e;
        }
    }

    protected function generateTrace(int $status, Response $response, \Throwable $e): Trace
    {
        return (new Trace())
            ->setIp($this->binUtilService->getCurrentIp())
            ->setCode($status)
            ->setResponse(
                json_decode($response->getContent() ?: '', true) ?: ['failure' => $response->getContent()]
            )
            ->setTrace($e->getTrace())
        ;
    }

    protected function saveRequest(Trace $trace, Request $request): void
    {
        $method = $request->getMethod();
        $trace->setEndpoint($request->getMethod() . ' ' . $request->getPathInfo());
        if ('GET' !== $method && 'HEAD' !== $method) {
            $trace->setPayload(json_decode($request->getContent(), true) ?? ['failure' => $request->getContent()]);
        } elseif ($request->getQueryString()) {
            parse_str($request->getQueryString(), $query);
            $trace->setPayload($query);
        }
    }
}
