<?php

namespace App\Service\Auth;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

/**
 * Class AuthAdapter
 * @package App\Service\Auth
 */
class AuthAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * AuthAdapter constructor
     */
    public function __construct(/* any dependencies */)
    {
        // Likely assign dependencies to properties
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->username = $email;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Result
     */
    public function authenticate()
    {
        // Retrieve the user's information (e.g. from a database)
        // and store the result in $row (e.g. associative array).
        // If you do something like this, always store the passwords using the
        // PHP password_hash() function!

        //  TODO - For now just return a success
        $identity = new \stdClass();
        $identity->name = $this->username;

        return new Result(Result::SUCCESS, $identity);

//        if (password_verify($this->password, $row['password'])) {
//            return new Result(Result::SUCCESS, $row);
//        }
//
//        return new Result(Result::FAILURE_CREDENTIAL_INVALID, $this->username);
    }
}
