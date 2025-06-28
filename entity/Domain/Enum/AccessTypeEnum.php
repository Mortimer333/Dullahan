<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Enum;

enum AccessTypeEnum: string
{
    case GET = 'get';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
}
