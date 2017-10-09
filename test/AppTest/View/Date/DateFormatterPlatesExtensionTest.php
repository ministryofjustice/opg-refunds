<?php

namespace AppTest\View\Date;

use App\Service\Date\IDate;
use App\View\Date\DateFormatterPlatesExtension;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DateFormatterPlatesExtensionTest extends TestCase
{
    /**
     * @var MockInterface|IDate
     */
    private $dateService;

    /**
     * @var DateFormatterPlatesExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->dateService = Mockery::mock(IDate::class);
        $this->extension = new DateFormatterPlatesExtension($this->dateService);
    }

    public function testGetDayAndFullTextMonth()
    {
        $formatted = $this->extension->getDayAndFullTextMonth(new DateTime('2017-09-25'));

        $this->assertEquals('25 September', $formatted);
    }

    public function testGetTimeIntervalAgoJustNow()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-25T09:18:38.000000+0000'));

        $this->assertEquals('Just now', $formatted);
    }

    public function testGetTimeIntervalAgo1MinuteAgo()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-25T09:18:37.000000+0000'));

        $this->assertEquals('1 minute ago', $formatted);
    }

    public function testGetTimeIntervalAgo2MinutesAgo()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-25T09:17:37.000000+0000'));

        $this->assertEquals('2 minutes ago', $formatted);
    }

    public function testGetTimeIntervalAgo59MinutesAgo()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-25T08:19:38.000000+0000'));

        $this->assertEquals('59 minutes ago', $formatted);
    }

    public function testGetTimeIntervalAgo1HourAgo()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-25T08:19:36.000000+0000'));

        $this->assertEquals('1 hour ago', $formatted);
    }

    public function testGetTimeIntervalAgo2HoursAgo()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-25T07:19:36.000000+0000'));

        $this->assertEquals('2 hours ago', $formatted);
    }

    public function testGetTimeIntervalYesterday()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-24T17:19:36.000000+0000'));

        $this->assertEquals('Yesterday', $formatted);
    }

    public function testGetTimeIntervalAgoTwoDays()
    {
        $this->dateService->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25T09:19:37.000000+0000'))->getTimestamp());

        $formatted = $this->extension->getTimeIntervalAgo(new DateTime('2017-09-23T12:19:36.000000+0000'));

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

        $this->assertEquals('09:19', $formatted);
    }
}