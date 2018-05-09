<?php

namespace App\Action\Poa;

use App\Action\Claim\AbstractClaimAction;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;

/**
 * Class AbstractPoaAction
 * @package App\Action\Poa
 */
abstract class AbstractPoaAction extends AbstractClaimAction
{
    /**
     * @param ClaimModel $claim
     * @return PoaModel
     */
    protected function getPoa(ClaimModel $claim)
    {
        if ($claim->getPoas() !== null) {
            foreach ($claim->getPoas() as $poa) {
                if ($poa->getId() == $this->modelId) {
                    return $poa;
                }
            }
        }

        return null;
    }
}