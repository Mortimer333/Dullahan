<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Http\Model\Definition;

use Dullahan\Main\Model\Definition\PointerEntityDTO;
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

    /**
     * @var array{
     *     code: PointerEntityDTO,
     * }
     */
    #[SWG\Property(type: 'object', properties: [
        new SWG\Property(property: 'code', ref: new Model(type: PointerEntityDTO::class)),
    ])]
    public array $pointers;
}
