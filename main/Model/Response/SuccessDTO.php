<?php

namespace Dullahan\Main\Model\Response;

use OpenApi\Attributes as SWG;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class SuccessDTO extends ResponseAbstractDTO
{
    #[SWG\Property(example: 'Successful request', description: 'Description of the successful request')]
    public string $message;
}
