<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use Zend\Validator\Callback;
use Zend\Validator\ValidatorInterface;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

/**
 * Form for collecting the user's contact details.
 *
 * Class ContactDetails
 * @package App\Form
 */
class ContactAddress extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------

        $field = new Element\Textarea('address');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StripTags);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
    }

}
