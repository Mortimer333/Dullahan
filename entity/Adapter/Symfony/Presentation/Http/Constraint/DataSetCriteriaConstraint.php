<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Presentation\Http\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class DataSetCriteriaConstraint extends PaginationConstraint
{
    public const SKIP = 'skip_column';

    public static function get(): Assert\Collection
    {
        // @TODO add tests and recursive check for orX and andX
        $methods = [
            'eq' => self::getMethod('Equal'),
            'gt' => self::getMethod('Greater'),
            'lt' => self::getMethod('Lesser'),
            'gte' => self::getMethod('Greater or Equal'),
            'lte' => self::getMethod('Lesser or Equal'),
            'neq' => self::getMethod('Not Equal'),
            'isNull' => self::getMethod('Is Null', self::SKIP),
            'in' => self::getMethod('In', self::getGroupOr('In')),
            'notIn' => self::getMethod('Not In', self::getGroupOr('Not In')),
        ];

        return new Assert\Collection([
            ...$methods,
            'andX' => new Assert\Optional([
                new Assert\Count(['min' => 1, 'minMessage' => 'And X cannot be empty']),
                new Assert\Type(['type' => 'array', 'message' => 'And X has to be an array']),
                new Assert\All(['constraints' => $methods]),
            ]),
            'orX' => new Assert\Optional([
                new Assert\Count(['min' => 1, 'minMessage' => 'Or X cannot be empty']),
                new Assert\Type(['type' => 'array', 'message' => 'Or X has to be an array']),
                new Assert\All(['constraints' => $methods]),
            ]),
        ]);
    }

    /**
     * @return array{0: Assert\Count, 1: Assert\Type, 2: Assert\All}
     */
    protected static function getGroupOr(string $name): array
    {
        return [
            new Assert\Count(['min' => 1, 'minMessage' => $name . ' cannot be empty']),
            new Assert\Type(['type' => 'array', 'message' => $name . ' has to be an array']),
            new Assert\All(['constraints' => [
                self::getDefaultValue(),
            ]]),
        ];
    }

    protected static function getDefaultValue(): Assert\AtLeastOneOf
    {
        return new Assert\AtLeastOneOf(['constraints' => [
            new Assert\Type([
                'type' => 'numeric',
                'message' => 'Value must be passed as a string or numeric',
            ]),
            new Assert\Type([
                'type' => 'string',
                'message' => 'Value must be passed as a string or numeric',
            ]),
        ]]);
    }

    protected static function getMethod(string $name, mixed $validation = null): Assert\Optional
    {
        if (is_null($validation)) {
            $validation = self::getDefaultValue();
        }
        $columnNameRegex = self::getColumnRegex();
        $collection = [
            [
                new Assert\NotBlank(['message' => 'Missing ' . $name . ' column']),
                new Assert\Type(['type' => 'string', 'message' => $name . ' column name must be a string']),
                new Assert\Regex([
                    'pattern' => '/\s/',
                    'match' => false,
                    'message' => $name . ' column name cannot contain whitespaces',
                ]),
                $columnNameRegex,
            ],
        ];
        $count = 2;
        if (self::SKIP != $validation) {
            $collection[] = $validation;
        } else {
            $count = 1;
        }

        return new Assert\Optional([
            new Assert\Count(['min' => 1, 'minMessage' => $name . ' cannot be empty']),
            new Assert\Type(['type' => 'array', 'message' => $name . ' has to be an array']),
            new Assert\All([
                new Assert\Type(['type' => 'array', 'message' => $name . ' has to be an array']),
                new Assert\Count([
                    'min' => $count,
                    'max' => $count,
                    'exactMessage' => $name . ' must have ' . $count . ' elements',
                    'minMessage' => $name . ' must have ' . $count . ' elements',
                    'maxMessage' => $name . ' must have ' . $count . ' elements',
                ]),
                new Assert\Collection($collection),
            ]),
        ]);
    }
}
