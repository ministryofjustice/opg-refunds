<?php
namespace App\Form;

use Laminas\Form\Form as LaminasForm;

use Laminas\Form\Element;
use Laminas\Filter;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

use Laminas\Validator\Callback;
use Laminas\Validator\ValidatorInterface;

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

        //------------------------
        // Notification opt-out

        $field = new Element\Checkbox('receive-notifications', [
            'checked_value' => 'no',
            'unchecked_value' => 'yes'
        ]);
        $input = new Input($field->getName());

        $input->setRequired(false);

        $this->add($field);
        $inputFilter->add($input);

        //---

        //---

        $this->addCsrfElement($inputFilter);
        $this->addCaseworkerNotesElement($inputFilter);
    }

    //-----------------------------

    public function getFormattedData()
    {
        $result = parent::getData();

        // Filter out empty values
        $result = array_filter($result);

        $result["receive-notifications"] = (bool)($result["receive-notifications"] == "yes");

        return $result;
    }

    public function setFormattedData(array $data)
    {
        $data["receive-notifications"] = ($data["receive-notifications"]) ? "yes" : "no";

        return $this->setData($data);
    }
}
