<?php

declare(strict_types=1);

namespace Dullahan\Asset\Presentation\HTTP\Model\Definition;

use OpenApi\Attributes as SWG;

class AssetEntityDTO
{
    #[SWG\Property]
    public int $id;

    #[SWG\Property(example: 'https://cod.boardmeister.com/media/image/dist/user/nick/image.png')]
    public string $src;

    #[SWG\Property(example: 1213212)]
    public int $weight;
}
