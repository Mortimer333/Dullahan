<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Response\Manage;

use Dullahan\Main\Model\Definition\UserEntityDTO;
use Dullahan\Main\Model\Response\SuccessDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class UserUpdatedDTO extends SuccessDTO
{
    #[SWG\Property(example: 'User updated successfully', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<mixed> $data
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'details', ref: new Model(type: UserEntityDTO::class)),
    ])]
    public array $data;
}