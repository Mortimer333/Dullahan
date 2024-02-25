<?php

declare(strict_types=1);

namespace Dullahan\Model\Parameter;

use OpenApi\Attributes as SWG;

class BulkDTO
{
    #[SWG\Property(
        property: 'namespace',
        type: 'string',
        example: 'monster',
        description: 'Entity namespace',
    )]
    public array $namespace;

    #[SWG\Property]
    public DataSetDTO $dataSet;

    #[SWG\Property]
    public PaginationDTO $pagination;

    #[SWG\Property]
    public bool $inherit = true;
}
