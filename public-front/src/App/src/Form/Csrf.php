<?php
namespace App\Form;

use Laminas\InputFilter\InputFilter;

/**
 * Generic form for when only a CSRF field is required.
 *
 * Class Csrf
 * @package App\Form
 */
class Csrf extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        $this->addCsrfElement($inputFilter);
    }
}
