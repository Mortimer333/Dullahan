<?php

declare(strict_types=1);

namespace Dullahan\Enum;

enum FieldTypeEnum: string
{
    case RICH = 'rich_content';
    case ENUM = 'enum';
}
