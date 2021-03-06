<?php
namespace App\Form;

use Laminas\Form\Element;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

use App\Validator;
use App\Filter\StandardInput as StandardInputFilter;

class DonorDetails extends AbstractForm
{

    public function __construct($options = [])
    {
        parent::__construct(self::class, $options);

        $isDonorDeceased = isset($options['isDonorDeceased']) ? $options['isDonorDeceased'] : false;

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
        // POA Name - Checkbox

        $field = new Element\Checkbox('poa-name-different', [
            'checked_value' => 'yes',
            'unchecked_value' => 'no'
        ]);
        $input = new Input($field->getName());

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // POA Name - Title

        $field = new Element\Text('poa-title');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // POA Name - First

        $field = new Element\Text('poa-first');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new StandardInputFilter);

        $input->getValidatorChain()
            ->attach(new Validator\NotEmpty, true)
            ->attach((new Validator\StringLength(['max' => 300])));

        $this->add($field);
        $inputFilter->add($input);


        //------------------------
        // POA Name - Last

        $field = new Element\Text('poa-last');
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

        $dob = new Fieldset\Dob();

        $this->add($dob);
        $inputFilter->add($dob->getInputFilter(), 'dob');

        if ($isDonorDeceased === false) {
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
        }

        //---

        $this->addCsrfElement($inputFilter);
        $this->addCaseworkerNotesElement($inputFilter);
    }

    //----------------------------------------------------------------

    public function setFormattedData(array $data)
    {
        $data['title'] = $data['current']['name']['title'] ?? null;
        $data['first'] = $data['current']['name']['first'] ?? null;
        $data['last'] = $data['current']['name']['last'] ?? null;

        if (isset($data['current']['address'])) {
            $data['address-1'] = $data['current']['address']['address-1'] ?? null;
            $data['address-2'] = $data['current']['address']['address-2'] ?? null;
            $data['address-3'] = $data['current']['address']['address-3'] ?? null;
            $data['address-postcode'] = $data['current']['address']['address-postcode'] ?? null;
        }

        $data['poa-title'] = $data['poa']['name']['title'] ?? null;
        $data['poa-first'] = $data['poa']['name']['first'] ?? null;
        $data['poa-last'] = $data['poa']['name']['last'] ?? null;

        if (isset($data['poa-first'])) {
            $data['poa-name-different'] = 'yes';
        }

        if (isset($data['current']['dob'])) {
            $dob = $data['current']['dob'];
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

        $response = [
            'poa' => [],
            'current' => []
        ];

        $response['current']['name'] = array_intersect_key($result, array_flip(['title','first','last']));

        if (isset($result['poa-first'])) {
            $response['poa']['name'] = [
                'title' => $result['poa-title'],
                'first' => $result['poa-first'],
                'last' => $result['poa-last'],
            ];
        }

        //---

        if (isset($result['address-1']) && isset($result['address-2']) && isset($result['address-3'])
            && isset($result['address-postcode'])) {
            $response['current']['address'] = array_intersect_key($result, array_flip([
                'address-1',
                'address-2',
                'address-3',
                'address-postcode'
            ]));
        }

        //---

        $response['current']['dob'] = $result['dob']['year'].'-'
            .sprintf('%02d', $result['dob']['month']).'-'
            .sprintf('%02d', $result['dob']['day']);

        return $response;
    }
}
