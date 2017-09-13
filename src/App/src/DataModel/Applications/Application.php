<?php

namespace App\DataModel\Applications;

use App\DataModel\AbstractDataModel;
use DateTime;

class Application extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $applicant;

    /**
     * @var Donor
     */
    protected $donor;

    /**
     * @var Attorney
     */
    protected $attorney;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @var Verification
     */
    protected $verification;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var DateTime
     */
    protected $submitted;

    /**
     * @var DateTime
     */
    protected $expected;

    /**
     * @return string
     */
    public function getApplicant(): string
    {
        return $this->applicant;
    }

    /**
     * @param string $applicant
     */
    public function setApplicant(string $applicant)
    {
        $this->applicant = $applicant;
    }

    /**
     * @return Donor
     */
    public function getDonor(): Donor
    {
        return $this->donor;
    }

    /**
     * @param Donor $donor
     */
    public function setDonor(Donor $donor)
    {
        $this->donor = $donor;
    }

    /**
     * @return Attorney
     */
    public function getAttorney(): Attorney
    {
        return $this->attorney;
    }

    /**
     * @param Attorney $attorney
     */
    public function setAttorney(Attorney $attorney)
    {
        $this->attorney = $attorney;
    }

    /**
     * @return Contact
     */
    public function getContact(): Contact
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return Verification
     */
    public function getVerification(): Verification
    {
        return $this->verification;
    }

    /**
     * @param Verification $verification
     */
    public function setVerification(Verification $verification)
    {
        $this->verification = $verification;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return DateTime
     */
    public function getSubmitted(): DateTime
    {
        return $this->submitted;
    }

    /**
     * @param DateTime $submitted
     */
    public function setSubmitted(DateTime $submitted)
    {
        $this->submitted = $submitted;
    }

    /**
     * @return DateTime
     */
    public function getExpected(): DateTime
    {
        return $this->expected;
    }

    /**
     * @param DateTime $expected
     */
    public function setExpected(DateTime $expected)
    {
        $this->expected = $expected;
    }

    protected function map($property, $value)
    {
        switch ($property) {
            case 'donor':
                return (($value instanceof Donor || is_null($value)) ? $value : new Donor($value));
            case 'attorney':
                return (($value instanceof Attorney || is_null($value)) ? $value : new Attorney($value));
            case 'contact':
                return (($value instanceof Contact || is_null($value)) ? $value : new Contact($value));
            case 'verification':
                return (($value instanceof Verification || is_null($value)) ? $value : new Verification($value));
            case 'account':
                return (($value instanceof Account || is_null($value)) ? $value : new Account($value));
            case 'submitted':
            case 'expected':
                return (($value instanceof DateTime || is_null($value)) ? $value : new DateTime($value));
            default:
                return parent::map($property, $value);
        }
    }
}