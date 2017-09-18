<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Exception;

abstract class AbstractEntity
{
    /**
     * Class of the datamodel that this entity can be converted to
     *
     * @var string
     */
    protected $dataModelClass;

    /**
     * Returns the entity as a datamodel structure
     *
     * @param array $customFieldMappings
     * @param array $excludeFilter
     * @return AbstractDataModel
     * @throws Exception
     */
    public function getAsDataModel(array $customFieldMappings = [], array $excludeFilter = [])
    {
        if (empty($this->dataModelClass)) {
            throw new Exception('Model class string must be provided for conversion');
        }

        $entityMethods = get_class_methods($this);

        $model = new $this->dataModelClass();

        foreach ($entityMethods as $entityMethod) {
            //  Must be a get method to continue
            if (strpos($entityMethod, 'get') !== 0) {
                continue;
            }

            //  Get the field name - excepting any mapping if provided
            $entityFieldName = substr($entityMethod, 3);
            $entityFieldName = (isset($customFieldMappings[$entityFieldName]) ? $customFieldMappings[$entityFieldName] : $entityFieldName);

            //  Exclude the field if required
            if (in_array($entityFieldName, $excludeFilter)) {
                continue;
            }

            //  Try to find a set method on the model and use it
            $entitySetMethod = 'set' . $entityFieldName;

            if (method_exists($model, $entitySetMethod)) {
                //  Determine which value to set
                $value = $this->$entityMethod();

                //  Don't set null values
                if (is_null($value)) {
                    continue;
                }

                //  Transfer the value from the entity field to the model field
                if ($value instanceof PersistentCollection) {
                    $collection = [];

                    foreach ($value as $i => $thisValue) {
                        $collection[] = $thisValue->getAsDataModel();
                    }

                    $value = $collection;
                }

                $model->$entitySetMethod($value);
            }
        }

        return $model;
    }
}