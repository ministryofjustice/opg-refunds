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

        $now = time();
        $diff = $now - $dateTime->getTimestamp();
        $diffInDays = floor($diff / 86400);

        if ($diffInDays === 0) {
            return 'Today';
        } elseif ($diffInDays === 1) {
            return '1 day ago';
        }

        return "$diffInDays days ago";
    }

    /**
     * @param DateTime $dateTime
     * @return false|string
     */
    public function getLogTimestamp($dateTime)
    {
        return date('d M Y \a\t H.i', $dateTime->getTimestamp());
    }
}
