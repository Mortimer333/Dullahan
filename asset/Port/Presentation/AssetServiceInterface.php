<?php

declare(strict_types=1);

namespace Dullahan\Asset\Port\Presentation;

use Dullahan\Main\Model\Context;

interface AssetServiceInterface
{
    public function validName(string $name, ?Context $context = null): bool;

    public function flush(?Context $context = null): void;

    public function clear(?Context $context = null): void;
}
