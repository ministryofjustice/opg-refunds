<?php

namespace App\Service\Refund;

interface DataHandlerInterface
{

    public function store(array $data) : string;

}
