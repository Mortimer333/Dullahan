<?php

declare(strict_types=1);

namespace Dullahan\Asset\Server;

use Dullahan\Contract\AssetManager\AssetInterface;
use Dullahan\Contract\AssetManager\AssetServerInterface;

class BigAssetServer implements AssetServerInterface
{
    public function serve(AssetInterface $asset): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $asset->getMimeType());
        header('Content-Disposition: inline; filename="' . $asset->getName() . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $asset->getWeight());

        $file = $asset->getFile();
        set_time_limit(0);
        while (!feof($file)) {
            echo fread($file, 1024 * 8);
            ob_flush();
            flush();
        }
    }
}
