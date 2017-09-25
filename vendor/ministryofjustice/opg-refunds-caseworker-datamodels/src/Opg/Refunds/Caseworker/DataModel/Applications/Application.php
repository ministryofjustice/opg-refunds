<?php

namespace Opg\Refunds\Caseworker\DataModel\Applications;

use Opg\Refunds\Caseworker\DataModel\AbstractDataModel;
use DateTime;

/**
 * Class Application
 * @package Opg\Refunds\Caseworker\DataModel\Applications
 */
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
     * @var CaseNumber
     */
    protected $caseNumber;

    /**
     * @var Postcodes
     */
    protected $postcodes;

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

    public function __construct($data = null)
    {
        //Make sure caseNumber and postcode objects are not null;
        $this->caseNumber = new CaseNumber();
        $this->postcodes = new Postcodes();

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getApplicant(): string
    {
        return $this->applicant;
    }

    /**
     * @param string $applicant
     * @return $this
     */
    public function setApplicant(string $applicant)
    {
        $this->applicant = $applicant;

        return $this;
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
     * @return $this
     */
    public function setDonor(Donor $donor)
    {
        $this->donor = $donor;

        return $this;
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
     * @return $this
     */
    public function setAttorney(Attorney $attorney)
    {
        $this->attorney = $attorney;

        return $this;
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
     * @return $this
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return CaseNumber
     */
    public function getCaseNumber(): CaseNumber
    {
        return $this->caseNumber;
    }

    /**
     * @param CaseNumber $caseNumber
     * @return Application
     */
    public function setCaseNumber(CaseNumber $caseNumber): Application
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /**
     * @return Postcodes
     */
    public function getPostcodes(): Postcodes
    {
        return $this->postcodes;
    }

    /**
     * @param Postcodes $postcodes
     * @return Application
     */
    public function setPostcodes(Postcodes $postcodes): Application
    {
        $this->postcodes = $postcodes;
        return $this;
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
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
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
     * @return $this
     */
    public function setSubmitted(DateTime $submitted)
    {
        $this->submitted = $submitted;

        return $this;
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
     * @return $this
     */
    public function setExpected(DateTime $expected)
    {
        $this->expected = $expected;

        return $this;
    }

    /**
     * Map properties to correct types
     *
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    protected function map($property, $value)
    {
        switch ($property) {
            case 'donor':
                return (($value instanceof Donor || is_null($value)) ? $value : new Donor($value));
            case 'attorney':
                return (($value instanceof Attorney || is_null($value)) ? $value : new Attorney($value));
            case 'contact':
                return (($value instanceof Contact || is_null($value)) ? $value : new Contact($value));
            case 'caseNumber':
                return (($value instanceof CaseNumber || is_null($value)) ? $value : new CaseNumber($value));
            case 'postcodes':
                return (($value instanceof Postcodes || is_null($value)) ? $value : new Postcodes($value));
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
