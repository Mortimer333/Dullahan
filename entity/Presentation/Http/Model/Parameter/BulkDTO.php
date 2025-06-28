<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Http\Model\Parameter;

use OpenApi\Attributes as SWG;

class BulkDTO
{
    #[SWG\Property(
        type: 'string',
        example: 'Blog/Post',
        description: 'Entity path',
    )]
    public string $path;

    #[SWG\Property]
    public DataSetDTO $dataSet;

    #[SWG\Property]
    public PaginationDTO $pagination;

    #[SWG\Property]
    public bool $inherit = true;
}
