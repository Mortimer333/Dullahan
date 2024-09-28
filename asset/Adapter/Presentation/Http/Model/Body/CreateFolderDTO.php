<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Http\Model\Body;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateFolderDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'Parent name must be a string')]
        #[Assert\NotBlank(message: 'Parent name cannot be empty')]
        public ?string $parent,
        #[Assert\Type(type: 'string', message: 'Folder name must be a string')]
        #[Assert\NotBlank(message: 'Folder name cannot be empty')]
        public ?string $name,
    ) {
    }
}
