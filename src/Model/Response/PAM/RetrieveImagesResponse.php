<?php

declare(strict_types=1);

namespace Dullahan\Model\Response\PAM;

use Dullahan\Model\Definition\AssetFullEntityDTO;
use Dullahan\Model\Response\SuccessDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class RetrieveImagesResponse extends SuccessDTO
{
    #[SWG\Property(example: 'Images retrieved successfully', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<mixed> $data
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'images', type: 'array', items: new SWG\Items(allOf: [
            new SWG\Property(ref: new Model(type: AssetFullEntityDTO::class)),
        ])),
    ])]
    public array $data;
}
