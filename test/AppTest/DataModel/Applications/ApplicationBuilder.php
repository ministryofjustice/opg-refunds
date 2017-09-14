<?php

namespace AppTest\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;

class ApplicationBuilder
{
    /**
     * @var Application
     */
    private $application;

    public function __construct()
    {
        $this->application = new Application();
    }

    /**
     * @return Application
     */
    public function build()
    {
        return $this->application;
    }

    public function withAccount(Account $account)
    {
        $this->application->setAccount($account);
        return $this;
    }
}