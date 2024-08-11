<?php

namespace Dullahan\Main\Model\Response\Authentication;

use Dullahan\Main\Model\Response\SuccessDTO;
use OpenApi\Attributes as SWG;

class ActivationResponseDTO extends SuccessDTO
{
    #[SWG\Property(example: 'User activated', description: 'Description of the successful request')]
    public string $message;
}
