<?php

namespace App\View\Date;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use DateTime;

/**
 * Class DateFormatterPlatesExtension
 * @package App\View\Date
 */
class DateFormatterPlatesExtension implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('getDayAndFullTextMonth', [$this, 'getDayAndFullTextMonth']);
        $engine->registerFunction('getTimeIntervalAgo', [$this, 'getTimeIntervalAgo']);
        $engine->registerFunction('getLogDateString', [$this, 'getLogDateString']);
        $engine->registerFunction('getLogTimeString', [$this, 'getLogTimeString']);
        $engine->registerFunction('getDateOfBirthString', [$this, 'getDateOfBirthString']);
        $engine->registerFunction('getReceivedDateString', [$this, 'getReceivedDateString']);
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getDayAndFullTextMonth($dateTime)
    {
        return $dateTime === null ? '' : date('d F', $dateTime->getTimestamp());
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getTimeIntervalAgo($dateTime)
    {
        if ($dateTime === null) {
            return '';
        }

        $now = time();
        $diff = $now - $dateTime->getTimestamp();
        $diffInMinutes = floor($diff/60);

        if ($diffInMinutes < 60) {
            if ($diffInMinutes === 0.0) {
                return 'Just now';
            } elseif ($diffInMinutes === 1.0) {
                return '1 minute ago';
            }
            return "$diffInMinutes minutes ago";
        }

        $diffInHours = floor($diff / 3600);

        if ($diffInHours < 24) {
            if (date('z', $dateTime->getTimestamp()) !== date('z', $now)) {
                return 'Yesterday';
            } elseif ($diffInHours === 1.0) {
                return '1 hour ago';
            }
            return "$diffInHours hours ago";
        }

        $diffInDays = ceil($diff / 86400);

        if ($diffInDays === 1.0) {
            return '1 day ago';
        }

        return "$diffInDays days ago";
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getLogDateString($dateTime)
    {
        return date('d M Y', $dateTime->getTimestamp());
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getLogTimeString($dateTime)
    {
        return date('H:i', $dateTime->getTimestamp());
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getDateOfBirthString($dateTime)
    {
        return date('d/m/Y', $dateTime->getTimestamp());
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getReceivedDateString($dateTime)
    {
        return date('d/m/Y', $dateTime->getTimestamp());
    }
}
