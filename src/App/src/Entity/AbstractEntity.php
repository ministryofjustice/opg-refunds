<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

abstract class AbstractEntity
{
    /**
     * @param array $excludeProperties Properties to remove from the array
     * @param array $includeChildren Only include complex child properties if they are included here
     * @return array
     */
    public function toArray($excludeProperties = [], $includeChildren = []): array
    {
        $values = get_object_vars($this);

        foreach ($excludeProperties as $excludeProperty) {
            unset($values[$excludeProperty]);
        }

        foreach ($values as $k => $v) {
            if ($v instanceof PersistentCollection && in_array($k, $includeChildren)) {
                $childValues = [];
                foreach ($v as $value) {
                    $childValues[] = $value->toArray();
                }
                $values[$k] = $childValues;
            }
        }

        return $values;
    }
}