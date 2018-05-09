<?php
namespace App\Service\Refund;

use App\Service\Session\Session;

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
            'name'      => 'apply.who',
            'requires'  => 'apply.who',
        ],
        [
            'name'      => 'apply.deceased',
            'requires'  => 'apply.deceased',
        ],
        [
            'name'      => 'apply.donor',
            'requires'  => 'apply.donor',
        ],
        [
            'name'      => 'apply.attorney',
            'requires'  => 'apply.attorney',
        ],
        [
            'name'      => 'apply.case',
            'requires'  => 'apply.case',
        ],
        [
            'name'      => 'apply.postcode',
            'requires'  => 'apply.postcode',
        ],
        [
            'name'      => 'apply.contact',
            'requires'  => 'apply.contact',
        ],
        [
            'name'      => 'apply.account',
            'requires'  => 'apply.account'
        ],
        [
            'name'      => 'apply.summary',
            'requires'  => 'apply.summary',
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
     * @param Session $session
     * @return bool
     */
    public static function routeAccessible(string $route, Session $session) : bool
    {
        // Determine the the index of the route they are trying to access is.
        $requiredIndexTheyAreAccessing = array_search(
            $route,
            array_column(self::$routes, 'name')
        );

        // Determine what page is required before they can access the route they are trying to access.
        $requiredRoute = self::$routes[$requiredIndexTheyAreAccessing]['requires'];

        //---

        // Find the index of the page required
        $requiredIndex = array_search(
            $requiredRoute,
            array_column(self::$routes, 'name')
        );

        // Finds the index of the page that's accessible next.
        $allowedIndex = array_search(
            self::getNextRouteName($session),
            array_column(self::$routes, 'name')
        );

        // Route is accessible if it's index is >= the current route's index.
        return ($requiredIndex <= $allowedIndex);
    }


    /**
     * Determines the next accessible route, based on the current session data.
     *
     * @param Session $session
     * @return string
     */
    public static function getNextRouteName(Session $session) : string
    {

        if (isset($session['reference'])) {
            return 'apply.done';
        }

        //---

        if (!isset($session['applicant'])) {
            return 'apply.who';
        }

        if ($session['applicant'] === 'attorney' && !isset($session['deceased'])) {
            return 'apply.deceased';
        }

        if (!isset($session['donor']['current']) || !is_array($session['donor']['current'])) {
            return 'apply.donor';
        }

        if (!isset($session['attorney']) || !is_array($session['attorney'])) {
            return 'apply.attorney';
        }

        if (!isset($session['case-number']) || !is_array($session['case-number'])) {
            return 'apply.case';
        }

        if (!isset($session['postcodes']) || !is_array($session['postcodes'])) {
            return 'apply.postcode';
        }

        if (!isset($session['contact']) || !is_array($session['contact'])) {
            return 'apply.contact';
        }


        if (
            (!isset($session['account']) || !is_array($session['account'])) &&
            (!isset($session['cheque']) || $session['cheque'] !== true)
        ) {
            return 'apply.account';
        }

        return 'apply.summary';
    }
}
