<?php

namespace App\Form;

class PoaMeris extends Poa
{
    public function __construct(array $options = [])
    {
        $this->system = 'meris';

        parent::__construct($options);
    }
}