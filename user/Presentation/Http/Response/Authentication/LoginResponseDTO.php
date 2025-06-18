<?php

namespace Dullahan\User\Presentation\Http\Response\Authentication;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class LoginResponseDTO extends SuccessDTO
{
    #[SWG\Property(example: 'User authenticated', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<string> $data
     */
    #[SWG\Property(
        example: '{'
            . '"token": "eyJhbGciOiJQUzI1NiIsImp0aSI6MSwiaXNzIjoiQm...bbZfGkcZHhXxlR2pLSRGeUpcovb0CdQb88nfWw",'
            . '"user": {'
                . '"roles" : ["ROLE_USER"],'
                . '"details" : {"id": 1, "email": "mail@mail.com", "data": {"name": "Username", "id": 1}}'
            . '},'
            . '"csrf": "19cb5be228b29bd932315f1cd88aa60dad6c1d982429d51b79d07a6fe082d67a.f752...3b4db0"'
            . '}',
        description: 'Authentication token',
        type: 'object',
        properties: [
            new SWG\Property(property: 'token', type: 'string'),
            new SWG\Property(property: 'user', type: 'object', properties: [
                new SWG\Property(property: 'roles', type: 'string[]'),
            ]),
        ]
    )]
    public array $data;
}
