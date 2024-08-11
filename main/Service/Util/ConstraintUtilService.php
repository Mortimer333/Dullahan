<?php

declare(strict_types=1);

namespace Dullahan\Main\Service\Util;

use Symfony\Component\Validator\Constraints as Assert;

class ConstraintUtilService
{
    /**
     * @param array<string, mixed> $constraint
     *
     * @return array<string, mixed|Assert\Optional>
     */
    public static function constraintToOptional(array $constraint): array
    {
        foreach ($constraint as $name => $item) {
            if (!is_array($item)) {
                continue;
            }
            $constraint[$name] = new Assert\Optional($item);
        }

        return $constraint;
    }
}
