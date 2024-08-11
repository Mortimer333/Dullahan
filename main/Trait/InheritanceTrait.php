<?php

declare(strict_types=1);

namespace Dullahan\Main\Trait;

trait InheritanceTrait
{
    public function getInherited(string $field): mixed
    {
        $getter = 'get' . ucfirst($field);
        if (!method_exists($this, $getter)) {
            throw new \Exception(sprintf("Method %s doesn't exist on %s", $getter, $this::class), 500);
        }

        if (is_null($this->$getter())) {
            $parent = $this->getParent();
            if ($parent) {
                return $parent->getInherited($field);
            }

            return null;
        }

        return $this->$getter();
    }

    public function getRootId(): ?int
    {
        $path = $this->getRelationPath();
        if (!is_string($path)) {
            return null;
        }

        $firstComma = strpos($path, ',');
        if (false === $firstComma) {
            return (int) $path;
        }

        return (int) substr($path, 0, $firstComma);
    }
}
