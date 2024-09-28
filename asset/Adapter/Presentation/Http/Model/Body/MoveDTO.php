<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Presentation\Http\Model\Body;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class MoveDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'From path must be a string')]
        #[Assert\NotBlank(message: 'From path cannot be empty')]
        public ?string $from,
        #[Assert\Type(type: 'string', message: 'To path must be a string')]
        #[Assert\NotBlank(message: 'To path cannot be empty')]
        public ?string $to,
    ) {
    }
}
