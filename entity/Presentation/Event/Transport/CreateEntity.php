<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 */
class CreateEntity extends EventAbstract
{
    /** @var T|null */
    public ?object $entity = null;

    /**
     * @param class-string<T>          $class
     * @param array<int|string, mixed> $payload
     */
    public function __construct(
        public readonly string $class,
        public array $payload,
        public bool $flush = true
    ) {
        parent::__construct();
    }
}
