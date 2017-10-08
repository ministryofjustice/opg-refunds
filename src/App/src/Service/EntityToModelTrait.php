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
     * @param string|null $dataModelClass
     * @return \Opg\Refunds\Caseworker\DataModel\AbstractDataModel
     */
    public function translateToDataModel($entity, string $dataModelClass = null)
    {
        if (!$entity instanceof AbstractEntity) {
            throw new InvalidInputException('Entity not found');
        }

        return $entity->getAsDataModel([], $dataModelClass);
    }

    /**
     * @param array $entities
     * @param string|null $dataModelClass
     * @return array
     */
    public function translateToDataModelArray($entities, string $dataModelClass = null)
    {
        if (!is_array($entities)) {
            throw new InvalidInputException('Entities not found');
        }

        $models = [];

        foreach ($entities as $i => $entity) {
            if ($entity instanceof AbstractEntity) {
                $models[$i] = $this->translateToDataModel($entity, $dataModelClass);
            }
        }

        return $models;
    }
}
