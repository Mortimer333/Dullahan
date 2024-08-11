<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Definition;

use OpenApi\Attributes as SWG;

class PointerEntityDTO
{
    #[SWG\Property]
    public int $id;

    #[SWG\Property]
    public string $class;

    #[SWG\Property]
    public string $column;

    #[SWG\Property]
    public int $entity;
}
