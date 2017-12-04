<?php

namespace AppTest\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\Applications\Account;
use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Applications\Contact;
use Opg\Refunds\Caseworker\DataModel\Applications\Donor;

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

    public function withDonor(Donor $donor)
    {
        $this->application->setDonor($donor);
        return $this;
    }

    public function withContact(Contact $contact)
    {
        $this->application->setContact($contact);
        return $this;
    }

    public function withAccount(Account $account)
    {
        $this->application->setAccount($account);
        return $this;
    }
}