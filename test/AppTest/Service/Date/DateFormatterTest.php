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

    public function testGetDaysAgoToday()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25'))->getTimestamp());

        $formatted = $this->dateFormatter->getDaysAgo(new DateTime('2017-09-25'));

        $this->assertEquals('Today', $formatted);
    }

    public function testGetDaysAgoOneDay()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25'))->getTimestamp());

        $formatted = $this->dateFormatter->getDaysAgo(new DateTime('2017-09-24'));

        $this->assertEquals('1 day ago', $formatted);
    }

    public function testGetDaysAgoTwoDays()
    {
        $this->dateProvider->shouldReceive('getTimeNow')->andReturn((new DateTime('2017-09-25'))->getTimestamp());

        $formatted = $this->dateFormatter->getDaysAgo(new DateTime('2017-09-23'));

        $this->assertEquals('2 days ago', $formatted);
    }

    public function testGetLogTimestamp()
    {
        $formatted = $this->dateFormatter->getLogTimestamp(new DateTime('2017-09-25T09:19:37.000000+0000'));

        $this->assertEquals('25 Sep 2017 at 09.19', $formatted);
    }
}