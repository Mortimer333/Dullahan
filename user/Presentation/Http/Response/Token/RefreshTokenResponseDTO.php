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
        example: '{"token": "eyJhbGciOiJQUzI1NiIsImp0aSI6MSwiaXNzIjoiQm...bbZfGkcZHhXxlR2pLSRGeUpcovb0CdQb88nfWw"}',
        description: 'Authentication token',
        type: 'object',
        properties: [
            new SWG\Property(property: 'token', type: 'string'),
        ]
    )]
    public array $data;
}
