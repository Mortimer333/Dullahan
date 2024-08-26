<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Mutator;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageMutatorService
{
    /**
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     *
     * @throws \ImagickException
     */
    public function stripMeta(UploadedFile $file): void
    {
        $imagick = new \Imagick($file->getRealPath());
        try {
            $icc_profile = $imagick->getImageProfile('icc');
        } catch (\ImagickException) {
        }

        try {
            $orientation = $imagick->getImageOrientation();
        } catch (\ImagickException) {
        }

        $imagick->stripImage();

        if (isset($icc_profile)) {
            $imagick->setImageProfile('icc', $icc_profile);
        }

        if (isset($orientation)) {
            $imagick->setImageOrientation($orientation);
        }

        $imagick->writeImage($file->getRealPath());
        $imagick->clear();
    }
}
