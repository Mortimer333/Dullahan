<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Parameter;

use Dullahan\Main\Model\Parameter\Pagination\SortItemDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class PaginationDTO
{
    #[SWG\Property(example: 10, description: 'The amount of rows to retrieve')]
    public int $limit;

    #[SWG\Property(example: 0, description: 'The amount of rows to retrieve')]
    public int $offset;

    /**
     * @var array<SortItemDTO>
     */
    #[SWG\Property(type: 'array', items: new SWG\Items(
        ref: new Model(type: SortItemDTO::class)
    ))]
    public array $sort;

    /**
     * @var array<string, mixed>
     */
    #[SWG\Property(
        type: 'array',
        example: '[["column", "=", "value"], "AND", ["column2","!=","value2"]]',
        items: new SWG\Items(anyOf: [
            new SWG\Property(type: 'array', minLength: 3, maxLength: 3, items: new SWG\Items(anyOf: [
                new SWG\Property(type: 'string', enum: [
                    '!=', '=', 'IS', 'IS NOT', '<', '>', '<>', '>=', '<=', 'LIKE',
                ]),
                new SWG\Property(type: 'string', example: 'column|value'),
            ])),
            new SWG\Property(
                type: 'array',
                example: '["date", "BETWEEN", "valueBefore", "AND", "valueAfter"]',
                minLength: 5,
                maxLength: 5,
                items: new SWG\Items(anyOf: [
                    new SWG\Property(type: 'string', enum: [
                        'BETWEEN', 'AND',
                    ]),
                    new SWG\Property(type: 'string', example: 'column|valueBefore|valueAfter'),
                ])
            ),
            new SWG\Property(type: 'string', enum: [
                'AND', 'OR', '(', ')',
            ]),
        ])
    )]
    public array $filter;

    /**
     * @var array<array<string>>
     */
    #[SWG\Property(type: 'array', items: new SWG\Items(anyOf: [
        new SWG\Property(
            type: 'array',
            minLength: 2,
            maxLength: 2,
            example: '["table", "alias"]',
            description: 'Allows to join related tables with chosen entity to achieve more complex filtering',
            items: new SWG\Items(anyOf: [
                new SWG\Property(type: 'string'),
            ])
        ),
    ]))]
    public array $join;
}
