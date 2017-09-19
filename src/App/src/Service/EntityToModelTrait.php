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
     * @return \Opg\Refunds\Caseworker\DataModel\AbstractDataModel
     */
    public function translateToDataModel($entity)
    {
        if (!$entity instanceof AbstractEntity) {
            throw new InvalidInputException('Entity not found');
        }

        return $entity->getAsDataModel();
    }

    /**
     * @param array $entities
     * @return array
     */
    public function translateToDataModelArray($entities)
    {
        if (!is_array($entities)) {
            throw new InvalidInputException('Entities not found');
        }

        $models = [];

        foreach ($entities as $i => $entity) {
            if ($entity instanceof AbstractEntity) {
                $models[$i] = $this->translateToDataModel($entity);
            }
        }

        return $models;
    }
}
