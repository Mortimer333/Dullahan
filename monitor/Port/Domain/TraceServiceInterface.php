<?php

declare(strict_types=1);

namespace Dullahan\Monitor\Port\Domain;

use Dullahan\Monitor\Domain\Entity\Trace;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface TraceServiceInterface
{
    public function create(\Throwable $e, ?Request $request = null, ?Response $response = null): ?Trace;
}
