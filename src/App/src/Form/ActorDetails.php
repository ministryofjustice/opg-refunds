<?php
namespace App\Form;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class ActorDetails extends AbstractForm
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
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Name - First

        $field = new Element\Text('first');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Name - Last

        $field = new Element\Text('last');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);

        //------------------------
        // DOB

        $dob = new Fieldset\Dob(
            ($options['dob-optional']) ?? false
        );

        $this->add($dob);
        $inputFilter->add($dob->getInputFilter(), 'dob');

        //---

        $this->addCsrfElement($inputFilter);
    }

    //----------------------------------------------------------------

    public function setFormattedData(array $data)
    {

        $data['title'] = $data['name']['title'] ?? null;
        $data['first'] = $data['name']['first'] ?? null;
        $data['last'] = $data['name']['last'] ?? null;

        if (isset($data['dob'])) {
            $dob = $data['dob'];
            $data['dob'] = [];
            list($data['dob']['year'],$data['dob']['month'],$data['dob']['day']) = explode('-', $dob);
        }

        return $this->setData($data);
    }

    public function getFormattedData()
    {
        $result = parent::getData();

        if (!is_array($result)) {
            return $result;
        }

        $response = [];

        $response['name'] = array_intersect_key($result, array_flip(['title','first','last']));

        //---

        $result['dob'] = array_filter( $result['dob'] );

        if (!empty($result['dob'])) {
            $response['dob'] = $result['dob']['year'].'-'
                .sprintf('%02d', $result['dob']['month']).'-'
                .sprintf('%02d', $result['dob']['day']);
        }

        //---

        return $response;
    }
}
