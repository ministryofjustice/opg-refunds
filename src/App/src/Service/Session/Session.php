<?php

namespace App\Service\Session;

use ArrayObject;

class Session extends ArrayObject
{
    const SESSION_IDENTITY_KEY = 'identity';

    /**
     * Set the logged in user identity
     *
     * @param $identity
     */
    public function setIdentity($identity)
    {
        $this[self::SESSION_IDENTITY_KEY] = $identity;
    }

    /**
     * Convenient way to get the user identity if they are logged in
     *
     */
    public function getIdentity()
    {
        return (isset($this[self::SESSION_IDENTITY_KEY]) ? $this[self::SESSION_IDENTITY_KEY] : null);
    }

    /**
     * Easy check for active session
     */
    public function loggedIn()
    {
        return !is_null($this->getIdentity());
    }

    /**
     * Destroy the session
     */
    public function destroy()
    {
        //  Empty the session contents completely so the session is destroyed by the session middleware
        $this->exchangeArray([]);
    }
}
