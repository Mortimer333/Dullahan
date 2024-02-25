<?php

declare(strict_types=1);

namespace Dullahan\Model\Definition;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class ThumbnailEntityDTO
{
    #[SWG\Property]
    public int $id;

    #[SWG\Property(example: 'https://cod.boardmeister.com/media/image/dist/user/nick/image.png')]
    public string $src;

    #[SWG\Property]
    public int $weight;

    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'code', ref: new Model(type: PointerEntityDTO::class)),
    ])]
    public array $pointers;
}
