<?php

namespace AppTest\DataModel\Cases;

use Opg\Refunds\Caseworker\DataModel\Applications\Application;
use Opg\Refunds\Caseworker\DataModel\Cases\Payment;
use Opg\Refunds\Caseworker\DataModel\Cases\RefundCase;
use App\Service\IdentFormatter;

class RefundCaseBuilder
{
    /**
     * @var RefundCase
     */
    private $case;

    public function __construct()
    {
        $this->case = new RefundCase();
        $this->case->setId(1234567890);
    }

    /**
     * @return RefundCase
     */
    public function build()
    {
        return $this->case;
    }

    /**
     * @param Application $application
     * @return RefundCaseBuilder $this
     */
    public function withApplication(Application $application)
    {
        $this->case->setApplication($application);
        return $this;
    }

    /**
     * @param Payment $payment
     * @return RefundCaseBuilder $this
     */
    public function withPayment(Payment $payment)
    {
        $this->case->setPayment($payment);
        return $this;
    }
}