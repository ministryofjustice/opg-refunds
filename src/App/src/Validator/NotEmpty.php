<?php
namespace App\Validator;

use Zend\Validator\NotEmpty as ZendNotEmpty;

class NotEmpty extends ZendNotEmpty
{

    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setMessage('required', self::IS_EMPTY);
    }
}
