<?php
namespace App\Validator;

use Zend\Validator\EmailAddress as ZendEmailAddress;

class EmailAddress extends ZendEmailAddress
{

    protected $messageTemplates = [
        self::INVALID            => "email-invalid",
        self::INVALID_FORMAT     => "email-invalid",
        self::INVALID_HOSTNAME   => "email-invalid",
        self::INVALID_MX_RECORD  => "email-invalid",
        self::INVALID_SEGMENT    => "email-invalid",
        self::DOT_ATOM           => "email-invalid",
        self::QUOTED_STRING      => "email-invalid",
        self::INVALID_LOCAL_PART => "email-invalid",
        self::LENGTH_EXCEEDED    => "email-invalid",
    ];
}
