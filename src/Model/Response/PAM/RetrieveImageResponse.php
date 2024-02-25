<?php

declare(strict_types=1);

namespace Dullahan\Model\Response\PAM;

use Dullahan\Model\Definition\AssetFullEntityDTO;
use Dullahan\Model\Response\SuccessDTO;
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
