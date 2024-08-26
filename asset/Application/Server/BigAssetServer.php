<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Server;

use Dullahan\Asset\Application\Port\Presentation\AssetServerInterface;
use Dullahan\Asset\Domain\Structure;

class BigAssetServer implements AssetServerInterface
{
    public function serve(Structure $asset): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $asset->mimeType);
        header('Content-Disposition: inline; filename="' . $asset->name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $asset->weight);

        $file = $asset->getResource();
        if (!$file) {
            throw new \Exception('File not present', 422);
        }

        set_time_limit(0);
        while (!feof($file)) {
            echo fread($file, 1024 * 8); // @phpstan-ignore-line Banned code
            ob_flush();
            flush();
        }
    }
}