<?php
namespace App\Service\Refund;

use ArrayObject, RuntimeException;

/**
 * Determine and return the route name of the next page the user must complete in the application flow.
 *
 * Class FlowController
 * @package App\Service\Refund
 */
class FlowController
{

    public static function getNextRouteName( ArrayObject $d )
    {

        if (!isset($d['types'])) {
            return 'apply.what';
        }

        if( in_array('hw', $d['types']) ){

            if( !isset($d['hw']) ){
                return 'apply.donor';
            }

        }

        throw new RuntimeException('Unable to find next route');
    }

}
