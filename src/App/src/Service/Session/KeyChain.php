<?php
namespace App\Service\Session;

use ArrayObject;
use UnexpectedValueException;

/**
 * Class KeyChain
 * @package App\Service\Session
 */
class KeyChain extends ArrayObject
{
    /**
     * KeyChain constructor
     *
     * @param array $keys
     */
    public function __construct(array $keys)
    {
        //  Loop through the array of keys and add them
        foreach ($keys as $key) {
            $items = explode(':', $key);

//            $value = hex2bin($items[1]);
            $value = $items[1];

            if (count($items) != 2 || mb_strlen($value, '8bit') < 32) {
                throw new UnexpectedValueException('Session encryption key is too short');
            }

            $this->offsetSet($items[0], $value);
        }
    }
}
