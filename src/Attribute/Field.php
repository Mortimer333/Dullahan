<?php

declare(strict_types=1);

namespace Dullahan\Attribute;

use Doctrine\Common\Collections\Criteria;

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
    ) {
        if (!is_null($this->order) && Criteria::ASC != $this->order && Criteria::DESC != $this->order) {
            throw new \Exception('Wrongly defined order, use Criteria constants', 500);
        }
    }
}
