<?php

declare(strict_types=1);

namespace Dullahan\Object\Presentation\Http\Model\Body;

use Dullahan\Object\Presentation\Http\Model\Parameter\DataSetDTO;
use OpenApi\Attributes as SWG;

class CreateUpdateBody
{
    #[SWG\Property]
    public DataSetDTO $dataSet;

    /**
     * @var array<string, mixed>
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'field', example: 'value'),
    ])]
    public array $entity;
}
