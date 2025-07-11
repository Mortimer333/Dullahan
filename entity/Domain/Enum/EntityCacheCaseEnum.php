<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Enum;

enum EntityCacheCaseEnum: string
{
    case SERIALIZATION = 'entity.serialization';
    case DEFINITION = 'entity.definition';
}
