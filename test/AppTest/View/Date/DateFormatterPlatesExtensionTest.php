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

    public function testGetTimeIntervalBetweenJustNow()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-25T09:18:38.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('Just now', $formatted);
    }

    public function testGetTimeIntervalBetween1MinuteAgo()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-25T09:18:37.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('1 minute ago', $formatted);
    }

    public function testGetTimeIntervalBetween2MinutesAgo()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-25T09:17:37.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('2 minutes ago', $formatted);
    }

    public function testGetTimeIntervalBetween59MinutesAgo()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-25T08:19:38.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('59 minutes ago', $formatted);
    }

    public function testGetTimeIntervalBetween1HourAgo()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-25T08:19:36.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('1 hour ago', $formatted);
    }

    public function testGetTimeIntervalBetween2HoursAgo()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-25T07:19:36.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('2 hours ago', $formatted);
    }

    public function testGetTimeIntervalYesterday()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-24T17:19:36.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('Yesterday', $formatted);
    }

    public function testGetTimeIntervalBetweenTwoDays()
    {
        $formatted = $this->extension->getTimeIntervalBetween(new DateTime('2017-09-23T12:19:36.000000+0000'), (new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $this->assertEquals('2 days ago', $formatted);
    }

    public function testGetNoteDateString()
    {
        $formatted = $this->extension->getNoteDateString(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('25 Sep 2017', $formatted);
    }

    public function testGetNoteTimeString()
    {
        $formatted = $this->extension->getNoteTimeString(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('10:19', $formatted);
    }
}