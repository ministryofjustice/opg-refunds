<?php

namespace App\Form;

class PoaSirius extends Poa
{
    public function __construct(array $options = [])
    {
        $this->system = 'sirius';

        parent::__construct($options);
    }
}