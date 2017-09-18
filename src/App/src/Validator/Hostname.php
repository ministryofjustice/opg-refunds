<?php
namespace App\Validator;

use Zend\Validator\Hostname as ZendHostname;

class Hostname extends ZendHostname
{
    protected $messageTemplates = [
        self::CANNOT_DECODE_PUNYCODE  => "hostname-invalid",
        self::INVALID                 => "hostname-invalid",
        self::INVALID_DASH            => "hostname-invalid",
        self::INVALID_HOSTNAME        => "hostname-invalid",
        self::INVALID_HOSTNAME_SCHEMA => "hostname-invalid",
        self::INVALID_LOCAL_NAME      => "hostname-invalid",
        self::INVALID_URI             => "hostname-invalid",
        self::IP_ADDRESS_NOT_ALLOWED  => "hostname-invalid",
        self::LOCAL_NAME_NOT_ALLOWED  => "hostname-invalid",
        self::UNDECIPHERABLE_TLD      => "hostname-invalid",
        self::UNKNOWN_TLD             => "hostname-invalid",
    ];
}
