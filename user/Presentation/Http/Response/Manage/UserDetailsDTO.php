<?php

declare(strict_types=1);

namespace Dullahan\User\Presentation\Http\Response\Manage;

use Dullahan\Main\Model\Response\SuccessDTO;
use Dullahan\User\Presentation\Http\Definition\UserEntityDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class UserDetailsDTO extends SuccessDTO
{
    #[SWG\Property(example: 'User details', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<mixed> $data
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'details', ref: new Model(type: UserEntityDTO::class)),
    ])]
    public array $data;
}
