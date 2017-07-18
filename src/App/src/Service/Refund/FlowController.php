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

    public static function getNextRouteName(ArrayObject $session, string $whoIsApplying)
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
