<?php

declare(strict_types=1);

namespace Dullahan\Model\Response\PAM;

use Dullahan\Model\Definition\AssetEntityDTO;
use Dullahan\Model\Response\SuccessDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class UploadImageResponse extends SuccessDTO
{
    #[SWG\Property(example: 'Image uploaded successfully', description: 'Description of the successful request')]
    public string $message;

    /**
     * @var array<mixed> $data
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'images', type: 'array', items: new SWG\Items(allOf: [
            new SWG\Property(ref: new Model(type: AssetEntityDTO::class)),
        ])),
    ])]
    public array $data;
}
