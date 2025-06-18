<?php

namespace Dullahan\User\Presentation\Http\Response\Token;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class RefreshTokenResponseDTO extends SuccessDTO
{
    #[SWG\Property(example: 'Token refreshed', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<string> $data
     */
    #[SWG\Property(
        example: '{
            "token": "eyJhbGciOiJQUzI1NiIsImp0aSI6MSwiaXNzIjoiQm...bbZfGkcZHhXxlR2pLSRGeUpcovb0CdQb88nfWw",
            "csrf": "19cb5be228b29bd932315f1cd88aa60dad6c1d982429d51b79d07a6fe082d67a.f752...3b4db0"
        }',
        description: 'Authentication token',
        type: 'object',
        properties: [
            new SWG\Property(property: 'token', type: 'string'),
            new SWG\Property(property: 'csrf', type: 'string'),
        ]
    )]
    public array $data;
}
