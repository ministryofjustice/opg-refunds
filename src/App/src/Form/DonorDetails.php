<?php
namespace App\Form;

use Zend\Form\FormInterface;
use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Validator;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator\NotEmpty;

class DonorDetails extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);

        //------------------------
        // Name - Title

        $field = new Element\Text('title');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StripTags());

        $input->getValidatorChain()->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Name - First

        $field = new Element\Text('first');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StripTags());

        $input->getValidatorChain()->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Name - Last

        $field = new Element\Text('last');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StripTags());

        $input->getValidatorChain()->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Day

        $field = new Element\Text('day');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StripTags());

        $input->getValidatorChain()->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Month

        $field = new Element\Text('month');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StripTags());

        $input->getValidatorChain()->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Year

        $field = new Element\Text('year');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StringTrim())
            ->attach(new Filter\StripTags());

        $input->getValidatorChain()->attach( new NotEmpty() );

        $this->add($field);
        $inputFilter->add($input);

    }


    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        $result = parent::getData($flag);

        if( !is_array($result) ){
            return $result;
        }

        $new = array();

        $new['name'] = array_intersect_key($result, array_flip(['title','first','last']));

        $new['dob'] = "{$result['year']}-{$result['month']}-{$result['day']}";

    }

}