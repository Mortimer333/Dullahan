<?php

namespace Dullahan\Model\Response\Authentication;

use Dullahan\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class RegistrationResponseDTO extends SuccessDTO
{
    #[SWG\Property(example: 'User registered', description: 'Description of the successful request')]
    public string $message;
}
