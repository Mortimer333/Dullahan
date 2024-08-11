<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Definition;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;

class AssetFullEntityDTO extends AssetEntityDTO
{
    /**
     * @var array<ThumbnailEntityDTO>
     */
    #[SWG\Property(type: 'array', items: new SWG\Items(allOf: [
        new SWG\Property(ref: new Model(type: ThumbnailEntityDTO::class)),
    ]))]
    public array $thumbnails;
}
