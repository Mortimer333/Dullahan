<?php

declare(strict_types=1);

namespace Dullahan\Model\Response\PEM;

use Dullahan\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class BulkResponse extends SuccessDTO
{
    #[SWG\Property(
        example: 'Bulk entities retrieved successfully',
        description: 'Description of the successful request'
    )]
    public string $message;

    /**
     * @var array<mixed> $data
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'bulk', type: 'object', properties: [
            new SWG\Property(property: 'name', type: 'object', properties: [
                new SWG\Property(property: 'entities', type: 'array', items: new SWG\Items(oneOf: [
                    new SWG\Property(type: 'object', properties: [
                        new SWG\Property(property: 'column', example: 'value'),
                    ]),
                ])),
                new SWG\Property(property: 'limit', type: 'integer', nullable: true),
                new SWG\Property(property: 'offset', type: 'integer', nullable: true),
                new SWG\Property(property: 'total', type: 'integer', nullable: true),
            ]),
        ]),
    ])]
    public array $data;
}
