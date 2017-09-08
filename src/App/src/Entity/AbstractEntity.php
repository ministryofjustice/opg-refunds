<?php

namespace App\Entity;

abstract class AbstractEntity
{
    /**
     * @param array $excludeProperties Properties to remove from the array
     * @return array
     */
    public function toArray($excludeProperties = []): array
    {
        $values = get_object_vars($this);

        foreach ($excludeProperties as $excludeProperty) {
            unset($values[$excludeProperty]);
        }

        return $values;
    }
}