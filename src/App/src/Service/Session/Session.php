<?php
namespace App\Service\Session;

class Session extends \ArrayObject
{
    /**
     * Clears all data out the session.
     */
    public function clear()
    {
        $this->exchangeArray([]);
    }
}
