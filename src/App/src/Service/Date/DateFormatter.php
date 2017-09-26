<?php

namespace App\Service\Date;

use DateTime;

/**
 * Class DateFormatter
 * @package App\Service\Date
 */
class DateFormatter
{
    /**
     * @var IDateProvider
     */
    private $dateProvider;

    public function __construct(IDateProvider $dateProvider)
    {
        $this->dateProvider = $dateProvider;
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
    public function getDaysAgo($dateTime)
    {
        if ($dateTime === null) {
            return '';
        }

        $now = $this->dateProvider->getTimeNow();
        $diff = $now - $dateTime->getTimestamp();
        $diffInDays = floor($diff / 86400);

        if ($diffInDays === 0.0) {
            return 'Today';
        } elseif ($diffInDays === 1.0) {
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
        return date('H.i', $dateTime->getTimestamp());
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
