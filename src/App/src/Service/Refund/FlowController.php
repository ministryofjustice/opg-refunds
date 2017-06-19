<?php
namespace App\Service\Refund;

use ArrayObject;

/**
 * Determine and return the route name of the next page the user must complete in the application flow.
 *
 * Class FlowController
 * @package App\Service\Refund
 */
class FlowController
{

    public static function getNextRouteName(ArrayObject $d)
    {

        if (!isset($d['types'])) {
            return 'apply.what';
        }

        //---

        $route = self::poaCheck('hw', $d);

        if (!is_null($route)) {
            return $route;
        }

        //---

        $route = self::poaCheck('pf', $d);

        if (!is_null($route)) {
            return $route;
        }

        //---

        $route = self::poaCheck('epa', $d);

        if (!is_null($route)) {
            return $route;
        }

        //---

        if (!isset($d['contact'])) {
            return 'apply.contact';
        }

        return 'apply.summary';
    }

    private static function poaCheck(string $type, ArrayObject $d)
    {
        if (in_array($type, $d['types'])) {
            if (!isset($d[$type]) || !isset($d[$type]['donor'])) {
                return "apply.donor.{$type}";
            }
        }

        return null;
    }
}
