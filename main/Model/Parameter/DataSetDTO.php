<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Parameter;

use OpenApi\Attributes as SWG;

class DataSetDTO
{
    /**
     * @var array<string>
     */
    #[SWG\Property(type: 'array', items: new SWG\Items(allOf: [
        new SWG\Property(type: 'string', example: 'fieldName'),
    ]))]
    public array $collectionField;
}
