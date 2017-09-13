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
     */
    public function setCaseNumber(string $caseNumber)
    {
        $this->caseNumber = $caseNumber;
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
     */
    public function setDonorPostcode(string $donorPostcode)
    {
        $this->donorPostcode = $donorPostcode;
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
     */
    public function setAttorneyPostcode(string $attorneyPostcode)
    {
        $this->attorneyPostcode = $attorneyPostcode;
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