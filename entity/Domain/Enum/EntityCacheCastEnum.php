<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Enum;

enum EntityCacheCastEnum: string
{
    case JSON_OBJECT = 'json-object';
    case JSON_ARRAY = 'json-array';
    case INT = 'int';
    case FLOAT = 'float';
    case ARRAY = 'array';
    case BOOL = 'bool';
    case OBJECT = 'object';
    case NONE = 'none';
}
