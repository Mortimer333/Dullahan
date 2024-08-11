<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Response\PAM;

use Dullahan\Main\Model\Definition\AssetFullEntityDTO;
use Dullahan\Main\Model\Response\SuccessDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class RetrieveImageResponse extends SuccessDTO
{
    #[SWG\Property(example: 'Image retrieved successfully', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<mixed> $data
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'image', ref: new Model(type: AssetFullEntityDTO::class)),
    ])]
    public array $data;
}
