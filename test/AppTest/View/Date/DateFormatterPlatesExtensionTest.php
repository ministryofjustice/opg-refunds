<?php

namespace AppTest\View\Date;

use App\View\Date\DateFormatterPlatesExtension;
use DateTime;
use PHPUnit\Framework\TestCase;

class DateFormatterPlatesExtensionTest extends TestCase
{
    /**
     * @var DateFormatterPlatesExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new DateFormatterPlatesExtension();
    }

    public function testGetDayAndFullTextMonth()
    {
        $formatted = $this->extension->getDayAndFullTextMonth(new DateTime('2017-09-25'));

        $this->assertEquals('25 September', $formatted);
    }

    public function testGetTimeIntervalAgoJustNow()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime());

        $this->assertEquals('Just now', $formatted);
    }

    public function testGetTimeIntervalAgo1MinuteAgo()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-1 minute'));

        $this->assertEquals('1 minute ago', $formatted);
    }

    public function testGetTimeIntervalAgo2MinutesAgo()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-2 minutes'));

        $this->assertEquals('2 minutes ago', $formatted);
    }

    public function testGetTimeIntervalAgo59MinutesAgo()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-59 minutes'));

        $this->assertEquals('59 minutes ago', $formatted);
    }

    public function testGetTimeIntervalAgo1HourAgo()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-1 hour'));

        $this->assertEquals('1 hour ago', $formatted);
    }

    public function testGetTimeIntervalAgo2HoursAgo()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-2 hours'));

        $this->assertEquals('2 hours ago', $formatted);
    }

    public function testGetTimeIntervalYesterday()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-23 hours'));

        $this->assertEquals('Yesterday', $formatted);
    }

    public function testGetTimeIntervalAgoTwoDays()
    {
        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('-2 days'));

        $this->assertEquals('2 days ago', $formatted);
    }

    public function testGetLogDateString()
    {
        $formatted = $this->extension->getLogDateString(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('25 Sep 2017', $formatted);
    }

    public function testGetLogTimeString()
    {
        $formatted = $this->extension->getLogTimeString(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('09:19', $formatted);
    }
}