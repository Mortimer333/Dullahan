<?php

declare(strict_types=1);

namespace Dullahan\Model\Body;

use Dullahan\Model\Parameter\DataSetDTO;
use OpenApi\Attributes as SWG;

class CreateUpdateBody
{
    #[SWG\Property]
    public DataSetDTO $dataSet;

    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'field', example: 'value'),
    ])]
    public array $entity;
}
