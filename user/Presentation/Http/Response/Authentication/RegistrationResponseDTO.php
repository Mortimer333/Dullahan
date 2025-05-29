<?php

namespace Dullahan\User\Presentation\Http\Response\Authentication;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class RegistrationResponseDTO extends SuccessDTO
{
    #[SWG\Property(example: 'User registered', description: 'Description of the successful request')]
    public string $message;
}
