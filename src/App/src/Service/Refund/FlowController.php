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
     * The order in which pages can be accessed.
     * And the page they 'require' before they can be accessed.
     *
     * @var array
     */
    private static $routes = [
        [
            'name'      => 'apply.donor',
            'requires'  => 'apply.donor',
        ],
        [
            'name'      => 'apply.attorney',
            'requires'  => 'apply.attorney',
        ],
        [
            'name'      => 'apply.verification',
            'requires'  => 'apply.verification',
        ],
        [
            'name'      => 'apply.contact',
            'requires'  => 'apply.contact',
        ],
        [
            'name'      => 'apply.summary',
            'requires'  => 'apply.summary',
        ],
        [
            'name'      => 'apply.account',
            'requires'  => 'apply.summary'
        ],
        [
            'name'      => 'apply.done',
            'requires'  => 'apply.done',
        ],
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

        // Find what route this route is dependent on.
        $requiredIndex = array_search(
            $route, array_column(self::$routes, 'name')
        );

        $requiredRoute = self::$routes[$requiredIndex]['requires'];

        //---

        $requiredIndex = array_search(
            $requiredRoute, array_column(self::$routes, 'name')
        );

        $allowedIndex = array_search(
            self::getNextRouteName($session, $whoIsApplying), array_column(self::$routes, 'name')
        );

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

        if (isset($session['reference'])) {
            return 'apply.done';
        }

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
