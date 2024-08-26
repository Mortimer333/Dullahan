<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Domain;

use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Structure;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailEntityInterface;

final readonly class Thumbnail
{
    public function __construct(
        public Structure $structure,
        public ThumbnailEntityInterface $entity,
        public Context $context,
    ) {
    }
}
