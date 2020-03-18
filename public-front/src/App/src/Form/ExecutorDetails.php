<?php
namespace App\Form;

use Laminas\Form\Element;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class ExecutorDetails extends AbstractForm
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
        // Address - line 1

        $field = new Element\Text('address-1');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Address - line 2

        $field = new Element\Text('address-2');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach((new Validator\StringLength(['max' => 300])));

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Address - line 3 (Town or city)

        $field = new Element\Text('address-3');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // Address - Postcode

        $field = new Element\Text('address-postcode');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);

        //---

        $this->addCsrfElement($inputFilter);
        $this->addCaseworkerNotesElement($inputFilter);
    }

    //----------------------------------------------------------------

    public function setFormattedData(array $data)
    {
        $data['title'] = $data['name']['title'] ?? null;
        $data['first'] = $data['name']['first'] ?? null;
        $data['last'] = $data['name']['last'] ?? null;

        $data['address-1'] = $data['address']['address-1'] ?? null;
        $data['address-2'] = $data['address']['address-2'] ?? null;
        $data['address-3'] = $data['address']['address-3'] ?? null;
        $data['address-postcode'] = $data['address']['address-postcode'] ?? null;

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

        $response['address'] = array_intersect_key($result, array_flip([
            'address-1',
            'address-2',
            'address-3',
            'address-postcode'
        ]));

        return $response;
    }
}
