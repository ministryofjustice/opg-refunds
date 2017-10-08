<?php

namespace App\Entity;

use Doctrine\ORM\PersistentCollection;
use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use Closure;
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
     * In the $modelToEntityMappings array key values reflect the set method to be used in the datamodel
     * for example a mapping of 'Something' => 'AnotherThing' will result in $model->setSomething($entity->getAnotherThing());
     * The value in the mapping array can also be a callback function
     *
     * @param array $modelToEntityMappings
     * @param string|null $dataModelClass
     * @return AbstractDataModel
     * @throws Exception
     */
    public function getAsDataModel(array $modelToEntityMappings = [], string $dataModelClass = null)
    {
        $dataModelClass = $dataModelClass ?: $this->dataModelClass;

        if (empty($dataModelClass)) {
            throw new Exception('Model class string must be provided for conversion');
        }

        $model = new $dataModelClass();

        //  Process any callbacks in the model to entity mappings then UNSET THEM
        foreach ($modelToEntityMappings as $modelFieldName => $callbackMapping) {
            if ($callbackMapping instanceof Closure) {
                //  Get the callback result and set the value (if not null)
                $value = call_user_func($callbackMapping);

                if (is_null($value)) {
                    continue;
                }

                //  Try to find a set method on the model and use it
                $modelSetMethod = 'set' . $modelFieldName;

                if (method_exists($model, $modelSetMethod)) {
                    $model->$modelSetMethod($value);
                }
            }
        }

        //  Loop through the entity methods and transfer the values to the datamodel
        $entityMethods = get_class_methods($this);

        foreach ($entityMethods as $entityMethod) {
            //  Must be a get or is method to continue
            $isGet = strpos($entityMethod, 'get') === 0;
            $isIs = strpos($entityMethod, 'is') === 0;

            if (!$isGet && !$isIs) {
                continue;
            }

            //  Get the field name (by default it will be the same for entity and model
            $entityFieldName = $modelFieldName = substr($entityMethod, $isIs ? 2 : 3);

            //  If there is a mapping for the model field name then swap that in
            if (in_array($entityFieldName, $modelToEntityMappings)) {
                $modelFieldName = array_search($entityFieldName, $modelToEntityMappings);
            } elseif (isset($modelToEntityMappings[$modelFieldName]) && $modelToEntityMappings[$modelFieldName] instanceof Closure) {
                //  Check to see if this model field has already been populated with a closure - if it has then move to the next method
                continue;
            }

            //  Try to find a set method on the model and use it
            $modelSetMethod = 'set' . $modelFieldName;

            if (method_exists($model, $modelSetMethod)) {
                $value = $this->$entityMethod();

                //  Don't set null values
                if (is_null($value)) {
                    continue;
                }

                //  Transfer the value from the entity field to the model field
                if ($value instanceof PersistentCollection) {
                    $collection = [];

                    foreach ($value as $i => $thisValue) {
                        //  TODO - Come up with a way to pass down $modelToEntityMappings if required at this stage
                        $collection[] = $thisValue->getAsDataModel();
                    }

                    $value = $collection;
                }

                //  Transfer the value from the entity field to the model field
                if ($value instanceof AbstractEntity) {
                    $value = $value->getAsDataModel();
                }

                $model->$modelSetMethod($value);
            }
        }

        return $model;
    }

    /**
     * Sets the entity data from the datamodel provided
     *
     * In the $entityToModelMappings array key values reflect the set method to be used in the entity
     * for example a mapping of 'Something' => 'AnotherThing' will result in $entity->setSomething($model->getAnotherThing());
     * The value in the mapping array can also be a callback function
     *
     * @param AbstractDataModel $model
     * @param array $entityToModelMappings
     * @throws Exception
     */
    public function setFromDataModel(AbstractDataModel $model, array $entityToModelMappings = [])
    {
        if (get_class($model) != $this->dataModelClass) {
            throw new Exception(sprintf('Unexpected datamodel (%s) used for population - expected %', get_class($model), $this->dataModelClass));
        }

        //  Loop through the entity methods and transfer the values from the datamodel
        $entityMethods = get_class_methods($this);

        $modelData = $model->getArrayCopy();

        foreach ($entityMethods as $entityMethod) {
            //  Must be a set method to continue
            if (strpos($entityMethod, 'set') !== 0) {
                continue;
            }

            //  Get the field name (by default it will be the same for entity and model
            $entityFieldName = $modelFieldName = substr($entityMethod, 3);

            if (isset($entityToModelMappings[$entityFieldName]) && $entityToModelMappings[$entityFieldName] instanceof Closure) {
                //  Get the callback result and set the value (if not null)
                $value = call_user_func($entityToModelMappings[$entityFieldName]);

                if (is_null($value)) {
                    continue;
                }

                //  Try to find a set method on the entity and use it
                $entitySetMethod = 'set' . $entityFieldName;

                if (method_exists($this, $entitySetMethod)) {
                    $this->$entitySetMethod($value);
                }
            } else {
                //  Translate the model field name to dash separated value and try to get it from the model data (array)
                $modelFieldName = strtolower(preg_replace('/([^A-Z-])([A-Z])/', '$1-$2', $modelFieldName));

                if (isset($modelData[$modelFieldName])) {
                    $value = $modelData[$modelFieldName];

                    //  Don't set null or none scalar values
                    //  TODO - Enhance this if it becomes required in the future
                    if (is_null($value) || !is_scalar($value)) {
                        continue;
                    }

                    $this->$entityMethod($value);
                }
            }
        }
    }
}
