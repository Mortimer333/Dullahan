<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\Event\Transport\Serialize;

use Dullahan\Asset\Domain\Entity\AssetPointer;
use Dullahan\Main\Model\Context;
use Dullahan\Main\Model\EventAbstract;

class AssetPointerSerializeEvent extends EventAbstract
{
    /**
     * @var array<string, mixed>
     */
    public array $serialized = [];

    public function __construct(
        public readonly AssetPointer $assetPointer,
        Context $context,
    ) {
        parent::__construct($context);
    }
}
