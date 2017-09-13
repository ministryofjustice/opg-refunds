<?php

namespace App\DataModel\Applications;

use App\DataModel\AbstractDataModel;

class Verification extends AbstractDataModel
{
    /**
     * @var string
     */
    protected $caseNumber;

    /**
     * @var string
     */
    protected $donorPostcode;

    /**
     * @var string
     */
    protected $attorneyPostcode;

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     * @return Verification $this
     */
    public function setCaseNumber(string $caseNumber)
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getDonorPostcode(): string
    {
        return $this->donorPostcode;
    }

    /**
     * @param string $donorPostcode
     * @return Verification $this
     */
    public function setDonorPostcode(string $donorPostcode)
    {
        $this->donorPostcode = $donorPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttorneyPostcode(): string
    {
        return $this->attorneyPostcode;
    }

    /**
     * @param string $attorneyPostcode
     * @return Verification $this
     */
    public function setAttorneyPostcode(string $attorneyPostcode)
    {
        $this->attorneyPostcode = $attorneyPostcode;
        return $this;
    }

    protected function populate(array $data)
    {
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'case-number':
                    $this->setCaseNumber($v);
                    break;
                case 'donor-postcode':
                    $this->setDonorPostcode($v);
                    break;
                case 'attorney-postcode':
                    $this->setAttorneyPostcode($v);
                    break;
            }
        }
    }
}
