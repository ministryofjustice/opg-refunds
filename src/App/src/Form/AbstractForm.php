<?php
namespace App\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\FormInterface;

use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

use App\Validator;
use Zend\Filter;

class AbstractForm extends ZendForm
{

    protected function addCsrfElement(InputFilter $inputFilter)
    {
        $options = $this->getOptions();

        $field = new Element\Csrf('secret');
        $input = new Input($field->getName());

        $field->setCsrfValidator(new Validator\Csrf([
            'name' => $this->getName(),
            'secret' => $options['csrf']
        ]));

        $input->getValidatorChain()->attach(new Validator\NotEmpty());

        $this->add($field);
        $inputFilter->add($input);
    }

    protected function addCaseworkerNotesElement(InputFilter $inputFilter)
    {
        $field = new Element\Textarea('notes');
        $input = new Input($field->getName());

        $input->getFilterChain()
            ->attach(new Filter\StripTags);

        $input->getValidatorChain()
            ->attach((new Validator\StringLength(['max' => 4000])));

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);
    }

    public function setData($data)
    {
        $options = $this->getOptions();

        // If no notes were passed, but we have some in option...
        if (!isset($data['notes']) && isset($options['notes'])) {
            $data['notes'] = $options['notes'];
        }

        return parent::setData($data);
    }

    /**
     * Function strips out the 'secret' value, if set.
     *
     * @param int $flag
     * @return array|object
     */
    public function getData($flag = FormInterface::VALUES_NORMALIZED)
    {
        $data = parent::getData($flag);
        if (is_array($data)) {
            unset($data['secret']);
        }

        // Never return notes here.
        unset($data['notes']);

        return $data;
    }

    /**
     * Returns the caseworker's notes.
     *
     * @return string
     */
    public function getNotes(){
        $data = parent::getData(FormInterface::VALUES_NORMALIZED);
        return ($data['notes']) ?? '';
    }
}
