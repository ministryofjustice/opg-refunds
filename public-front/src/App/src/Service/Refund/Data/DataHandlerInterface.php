<?php
namespace App\Service\Refund\Data;

interface DataHandlerInterface
{

    public function store(array $data) : string;
}
