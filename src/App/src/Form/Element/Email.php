<?php

namespace App\Form\Element;

use Zend\Validator\Regex as RegexValidator;

use Zend\Form\Element\Email as ZfEmailElement;

/**
 * Class Email
 * @package App\Form\Element
 */
class Email extends ZfEmailElement
{
    /**
     * Email constructor
     *
     * @param null $name
     * @param array $options
     */
    public function __construct($name = null, array $options = [])
    {
        parent::__construct($name, $options);

        //  Add custom email validator to ensure that only specific email address domains can be used
        $this->emailValidator = new RegexValidator(
            '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@(publicguardian.gsi.gov.uk|digital.justice.gov.uk)/'
        );
    }
}
