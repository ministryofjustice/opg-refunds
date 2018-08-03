<?php
namespace App\Service\Refund\Data;

/**
 * Place where phone number logic lives.
 *
 */
class PhoneNumber
{
    private $number;

    public function __construct(string $number)
    {
        $this->number = preg_replace('/^[+]?[0]*44/', '0', $number);
    }

    public function get() : string
    {
        return $this->number;
    }

    public function __toString()
    {
        return $this->get();
    }

    public function isMobile() : bool
    {
        return (bool)preg_match('/^07/', $this->number) && !preg_match('/^070/', $this->number);
    }
}
