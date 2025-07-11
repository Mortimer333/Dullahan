<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T
 */
class GetEntityCache extends EventAbstract
{
    /** @var T|null */
    public mixed $cached = null;
    public bool $isHit = false;

    public function __construct(
        public string $key,
        public readonly string $case,
        public string $cast,
    ) {
        parent::__construct();
    }

    public function get(): mixed
    {
        return $this->cached;
    }
}
