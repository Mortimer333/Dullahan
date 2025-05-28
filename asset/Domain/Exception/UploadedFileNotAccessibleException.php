<?php

declare(strict_types=1);

namespace Dullahan\Asset\Domain\Exception;

class UploadedFileNotAccessibleException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Uploaded file is not accessible', 500);
    }
}
