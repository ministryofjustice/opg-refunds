<?php
namespace App\Validator;

use Zend\Validator\Csrf as ZendCsrf;

/**
 * Simplified CSRF validator that relies on a passed secret.
 * Where the secret comes from is beyond the scope of this class.
 *
 * Class Csrf
 * @package App\Validator
 */
class Csrf extends ZendCsrf
{

    protected $messageTemplates = [
        self::NOT_SAME => "csrf",
    ];

    public function __construct($options = [])
    {
        parent::__construct($options);

        if (!isset($options['secret']) || strlen($options['secret']) < 64) {
            throw new \InvalidArgumentException('A (64 character) CSRF secret is required');
        }

        $this->hash = $options['secret'];
    }

    public function isValid($value, $context = null)
    {
        if ($value !== $this->getHash(true)) {
            $this->error(self::NOT_SAME);
            return false;
        }
        return true;
    }

    public function getHash($regenerate = false)
    {
        $name = $this->getName();

        if (!is_string($name) || strlen($name) == 0) {
            throw new \UnexpectedValueException('CSRF name needs to be set');
        }

        return hash('sha512', $this->hash.$name);
    }
}
