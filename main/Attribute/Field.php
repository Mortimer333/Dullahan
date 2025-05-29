<?php

declare(strict_types=1);

namespace Dullahan\Main\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    /**
     * @param array<class-string> $important
     * @param array<mixed>        $auto
     */
    public function __construct(
        public string $relation = '',
        public array $important = [],
        public ?string $order = null,
        public ?int $limit = null,
        public mixed $type = null,
        public ?array $auto = null,
        public ?string $plural = null,
        public ?string $enum = null,
    ) {
        if (!is_null($this->order) && 'ASC' != $this->order && 'DESC' != $this->order) {
            throw new \Exception('Order must be either ASC or DESC', 500);
        }
    }
}
