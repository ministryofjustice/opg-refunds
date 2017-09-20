<?php

namespace AppTest\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim;

class ClaimBuilder
{
    /**
     * @var Claim
     */
    private $claim;

    public function __construct()
    {
        $this->claim = new Claim();
        $this->claim->setId(1234567890);
    }

    /**
     * @return Claim
     */
    public function build()
    {
        return $this->claim;
    }

    /**
     * @param Application $application
     * @return ClaimBuilder $this
     */
    public function withApplication(Application $application)
    {
        $this->claim->setApplication($application);
        return $this;
    }

    /**
     * @param Payment $payment
     * @return ClaimBuilder $this
     */
    public function withPayment(Payment $payment)
    {
        $this->claim->setPayment($payment);
        return $this;
    }
}