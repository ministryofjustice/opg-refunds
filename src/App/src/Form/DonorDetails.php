<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;

use Zend\Form\Element;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class DonorDetails extends ZendForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $inputFilter = new InputFilter;
        $this->setInputFilter($inputFilter);

        //------------------------
        // Name - Title

        $field = new Element\Text('title');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty, true )
            ->attach( (new Validator\StringLength(['max' => 300])) );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Name - First

        $field = new Element\Text('first');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty, true )
            ->attach( (new Validator\StringLength(['max' => 300])) );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Name - Last

        $field = new Element\Text('last');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty, true )
            ->attach( (new Validator\StringLength(['max' => 300])) );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Day

        $field = new Element\Text('day');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty, true )
            ->attach( new Validator\Digits, true )
            ->attach( new Validator\Between( ['min'=>1, 'max'=>31] ), true );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Month

        $field = new Element\Text('month');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty, true )
            ->attach( new Validator\Digits, true )
            ->attach( new Validator\Between( ['min'=>1, 'max'=>12] ), true );

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // DOB - Year

        $field = new Element\Text('year');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach( new Validator\NotEmpty, true )
            ->attach( new Validator\Digits, true )
            ->attach( new Validator\Between( ['min'=>1800, 'max'=>date('Y')] ), true );

        $this->add($field);
        $inputFilter->add($input);
    }

    //----------------------------------------------------------------

    public function setFormattedData(array $data)
    {

        $data['title'] = $data['name']['title'] ?? null;
        $data['first'] = $data['name']['first'] ?? null;
        $data['last'] = $data['name']['last'] ?? null;

        if (isset($data['dob'])) {
            list($data['year'],$data['month'],$data['day']) = explode('-', $data['dob']);
        }

        return $this->setData($data);
    }

    public function getFormattedData()
    {
        $result = parent::getData();

        if (!is_array($result)) {
            return $result;
        }

        $response = array();

        $response['name'] = array_intersect_key($result, array_flip(['title','first','last']));

        $response['dob'] = $result['year'].'-'.sprintf('%02d', $result['month']).'-'.sprintf('%02d', $result['day']);

        return $response;
    }

}