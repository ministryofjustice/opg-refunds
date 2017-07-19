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

    /**
     * The order in which pages are accessible
     *
     * @var array
     */
    private static $order = [
        'apply.donor',
        'apply.attorney',
        'apply.verification',
        'apply.contact',
        'apply.summary',
        'apply.account',
        'apply.done',
    ];


    /**
     * Determines if the passed $route is accessible, based on the current session data.
     *
     * @param string $route
     * @param ArrayObject $session
     * @param string $whoIsApplying
     * @return bool
     */
    public static function routeAccessible(string $route, ArrayObject $session, string $whoIsApplying) : bool
    {
        $requiredIndex = array_search($route, self::$order);
        $allowedIndex = array_search(self::getNextRouteName($session, $whoIsApplying), self::$order);

        // Route is accessible if it's index is >= the current route's index.
        return ($requiredIndex <= $allowedIndex);
    }


    /**
     * Determines the next accessible route, ased on the current session data.
     *
     * @param ArrayObject $session
     * @param string $whoIsApplying
     * @return string
     */
    public static function getNextRouteName(ArrayObject $session, string $whoIsApplying) : string
    {

        if (!isset($session['donor']) || !is_array($session['donor'])) {
            return 'apply.donor';
        }

        if ($whoIsApplying === 'attorney' && (!isset($session['attorney']) || !is_array($session['attorney']))) {
            return 'apply.attorney';
        }

        if (!isset($session['verification']) || !is_array($session['verification'])) {
            return 'apply.verification';
        }

        if (!isset($session['contact']) || !is_array($session['contact'])) {
            return 'apply.contact';
        }

        return 'apply.summary';
    }
}
