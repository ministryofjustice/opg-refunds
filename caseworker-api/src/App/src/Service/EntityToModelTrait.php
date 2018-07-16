<?php

namespace App\Service;

use App\Entity\AbstractEntity;
use App\Exception\InvalidInputException;

/**
 * Trait to assist with entity to model translation
 *
 * Trait EntityToModelTrait
 * @package App\Service
 */
trait EntityToModelTrait
{
    /**
     * @param AbstractEntity $entity
     * @param array $modelToEntityMappings
     * @param string|null $dataModelClass
     * @return \Opg\Refunds\Caseworker\DataModel\AbstractDataModel
     */
    public function translateToDataModel($entity, array $modelToEntityMappings = [], string $dataModelClass = null)
    {
        if (!$entity instanceof AbstractEntity) {
            throw new InvalidInputException('Entity not found');
        }

        if ($dataModelClass === null) {
            return $entity->getAsDataModel($modelToEntityMappings);
        }

        return $entity->getAsDataModel($modelToEntityMappings, $dataModelClass);
    }

    /**
     * @param array $entities
     * @param array $modelToEntityMappings
     * @param string|null $dataModelClass
     * @return array
     */
    public function translateToDataModelArray($entities, array $modelToEntityMappings = [], string $dataModelClass = null)
    {
        if (!is_array($entities)) {
            throw new InvalidInputException('Entities not found');
        }

        $models = [];

        foreach ($entities as $i => $entity) {
            if ($entity instanceof AbstractEntity) {
                $models[$i] = $this->translateToDataModel($entity, $modelToEntityMappings, $dataModelClass);
            }
        }

        return $models;
    }
}
