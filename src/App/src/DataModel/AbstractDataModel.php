<?php

namespace App\DataModel;

use InvalidArgumentException;

class AbstractDataModel
{
    /**
     * Builds and populates $this data model object.
     *
     * If $data is:
     *  - null: Nothing is populated.
     *  - string: We attempt to JSON decode the string and populate the object.
     *  - string: We populate the object from the array.
     *
     * @param null|string|array $data
     */
    public function __construct($data = null)
    {
        // If it's a string...
        if (is_string($data)) {
            // Assume it's JSON.
            $data = json_decode($data, true);

            // Throw an exception if it turns out to not be JSON...
            if (is_null($data)) {
                throw new InvalidArgumentException('Invalid JSON passed to constructor');
            }
        }

        // If it's [now] an array...
        if (is_array($data)) {
            $this->populate($data);
        } elseif (!is_null($data)) {
            // else if it's not null (or array) now, it was an invalid data type...
            throw new InvalidArgumentException('Invalid argument passed to constructor');
        }
    }

    /**
     * Populates the concrete class' properties with the array.
     *
     * @param array $data
     * @return self
     */
    protected function populate(array $data)
    {
        // Foreach each passed property...
        foreach ($data as $k => $v) {
            // Only include known properties during the import...
            if (property_exists($this, $k) && !is_null($v)) {
                $this->{$k} = $this->map($k, $v);
            }
        }

        return $this;
    }

    /**
     * Basic mapper. This should be overridden in the concrete class if needed.
     * This is included here to ensure the method is always available
     * and - by default - returns the original value it was passed.
     *
     * @param $property string The property name.
     * @param $value mixed The value we've been passed.
     * @return mixed The potentially updated value.
     */
    protected function map($property, $value)
    {
        return $value;
    }
}
