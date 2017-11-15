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

    /**
     * @var AssistedDigital
     */
    protected $ad;

    /**
     * @var bool
     */
    protected $cheque;

    /**
     * @var bool
     */
    protected $deceased;

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
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @param CaseNumber $caseNumber
     * @return $this
     */
    public function setCaseNumber(CaseNumber $caseNumber): Application
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasCaseNumber(): bool
    {
        return $this->caseNumber !== null && $this->caseNumber->hasPoaCaseNumber();
    }

    /**
     * @return Postcodes
     */
    public function getPostcodes()
    {
        return $this->postcodes;
    }

    /**
     * @param Postcodes $postcodes
     * @return $this
     */
    public function setPostcodes(Postcodes $postcodes): Application
    {
        $this->postcodes = $postcodes;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPostcodes(): bool
    {
        return $this->postcodes !== null
            && $this->postcodes->hasDonorPostcode()
            && $this->postcodes->hasAttorneyPostcode();
    }

    /**
     * @return bool
     */
    public function hasDonorPostcode(): bool
    {
        return $this->postcodes !== null
            && $this->postcodes->hasDonorPostcode();
    }

    /**
     * @return bool
     */
    public function hasAttorneyPostcode(): bool
    {
        return $this->postcodes !== null
            && $this->postcodes->hasAttorneyPostcode();
    }

    /**
     * @return Account
     */
    public function getAccount()
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
     * @return bool
     */
    public function hasAccount(): bool
    {
        return $this->account !== null;
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
     * @return AssistedDigital
     */
    public function getAssistedDigital()
    {
        return $this->ad;
    }

    /**
     * @param AssistedDigital $assistedDigital
     * @return $this
     */
    public function setAssistedDigital(AssistedDigital $assistedDigital)
    {
        $this->ad = $assistedDigital;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAssistedDigital(): bool
    {
        return $this->ad !== null;
    }

    /**
     * @return bool
     */
    public function isRefundByCheque(): bool
    {
        return $this->cheque !== null && $this->cheque;
    }

    /**
     * @param bool $refundByCheque
     * @return $this
     */
    public function setRefundByCheque(bool $refundByCheque)
    {
        $this->cheque = $refundByCheque;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDonorDeceased(): bool
    {
        return $this->deceased !== null && $this->deceased;
    }

    /**
     * @param bool $donorDeceased
     * @return $this
     */
    public function setDonorDeceased(bool $donorDeceased)
    {
        $this->deceased = $donorDeceased;

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
            case 'ad':
                return (($value instanceof AssistedDigital || is_null($value)) ? $value : new AssistedDigital($value));
            default:
                return parent::map($property, $value);
        }
    }
}
