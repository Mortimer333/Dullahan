<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Definition;

use OpenApi\Attributes as SWG;

class UserEntityDTO
{
    #[SWG\Property]
    public int $id;

    #[SWG\Property]
    public UserDataEntityDTO $data;

    #[SWG\Property]
    public string $email;

    #[SWG\Property]
    public bool $active;

    #[SWG\Property(example: '2020-01-01 00:00:00')]
    public string $created;

    /**
     * @var array{
     *     limit: int,
     *     taken: int,
     * }
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'limit', type: 'int', example: '1000000'),
        new SWG\Property(property: 'taken', type: 'int', example: '33242'),
    ])]
    public array $storage;
}
