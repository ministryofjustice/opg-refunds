<?php

namespace AppTest\Service\Date;

use App\Service\Date\DateFormatter;
use App\Service\Date\IDateProvider;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    /**
     * @var MockInterface|IDateProvider
     */
    private $dateProvider;
    /**
     * @var DateFormatter
     */
    private $dateFormatter;

    protected function setUp()
    {
        $this->dateProvider = Mockery::mock(IDateProvider::class);
        $this->dateFormatter = new DateFormatter($this->dateProvider);
    }

    public function testGetDayAndFullTextMonth()
    {
        $formatted = $this->dateFormatter->getDayAndFullTextMonth(new DateTime('2017-09-25'));

        $this->assertEquals('25 September', $formatted);
    }

    public function testGetTimeIntervalAgoJustNow()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-25T09:18:38.000000+0000'));

        $this->assertEquals('Just now', $formatted);
    }

    public function testGetTimeIntervalAgo1MinuteAgo()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-25T09:18:37.000000+0000'));

        $this->assertEquals('1 minute ago', $formatted);
    }

    public function testGetTimeIntervalAgo2MinutesAgo()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-25T09:17:37.000000+0000'));

        $this->assertEquals('2 minutes ago', $formatted);
    }

    public function testGetTimeIntervalAgo59MinutesAgo()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-25T08:19:38.000000+0000'));

        $this->assertEquals('59 minutes ago', $formatted);
    }

    public function testGetTimeIntervalAgo1HourAgo()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-25T08:19:36.000000+0000'));

        $this->assertEquals('1 hour ago', $formatted);
    }

    public function testGetTimeIntervalAgo2HoursAgo()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-25T07:19:36.000000+0000'));

        $this->assertEquals('2 hours ago', $formatted);
    }

    public function testGetTimeIntervalYesterday()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-24T17:19:36.000000+0000'));

        $this->assertEquals('Yesterday', $formatted);
    }

    public function testGetTimeIntervalAgoTwoDays()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->dateFormatter->getTimeIntervalAgo(new DateTime('2017-09-23T12:19:36.000000+0000'));

        $this->assertEquals('2 days ago', $formatted);
    }

    public function testGetLogDateString()
    {
        $formatted = $this->dateFormatter->getLogDateString(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('25 Sep 2017', $formatted);
    }

    public function testGetLogTimeString()
    {
        $formatted = $this->dateFormatter->getLogTimeString(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('09:19', $formatted);
    }
}