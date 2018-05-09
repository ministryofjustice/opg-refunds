<?php

namespace AppTest\Spreadsheet;

use App\Spreadsheet\SortCodeMapper;
use PHPUnit\Framework\TestCase;

class SortCodeMapperTest extends TestCase
{
    public function testGetNameOfBank()
    {
        $sortCodeMappings = [
            '544167' => 'National Westminster Bank',
            '402012' => 'HSBC Bank',
            '403330' => 'HSBC Bank',
            '090128' => 'Santander UK',
            '601015' => 'National Westminster Bank',
            '090128' => 'Santander UK',
            '070116' => 'Nationwide Building Society',
            '515011' => 'National Westminster Bank',
            '203616' => 'Barclays Bank',
            '110808' => 'Halifax',
            '800214' => 'Bank of Scotland',
            '402531' => 'HSBC Bank',
        ];

        foreach ($sortCodeMappings as $sortCode => $nameOfBank) {
            $mappedNameOfBank = SortCodeMapper::getNameOfBank($sortCode);
            $this->assertEquals($nameOfBank, $mappedNameOfBank);
        }
    }
}