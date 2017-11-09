<?php

namespace App\Spreadsheet;

/**
 * Class SortCodeMapper
 * @package App\Spreadsheet
 */
class SortCodeMapper
{
    public static function getNameOfBank(string $sortCode)
    {
        $sortCode = str_replace('-', '', $sortCode);

        $group1 = substr($sortCode, 0, 2);
        $group2 = substr($sortCode, 2, 2);
        $group3 = substr($sortCode, 4, 2);

        switch ($group1) {
            case '01':
                return 'National Westminster Bank';
            case '04':
                if ($group2 === '00') {
                    if ($group3 === '04') {
                        return 'Monzo Bank';
                    } elseif ($group3 === '40') {
                        return 'Starling Bank';
                    }
                } elseif ($group2 === '04' && $group3 === '05') {
                    return 'ClearBank';
                }
                break;
            case '05':
                return 'Yorkshire Bank';
            case '07':
                return 'Nationwide Building Society';
            case '08':
                if ($group2 >= 30 && $group2 <= 30) {
                    return 'Citibank';
                }
                return 'The Co-operative Bank';
            case '09':
                return 'Santander UK';
            case '10':
                return 'Bank of England';
            case '11':
                return 'Halifax';
            case '12':
                return 'Sainsbury\'s Bank';
            case '13':
            case '14':
                return 'Barclays Bank';
            case '15':
                if ($group2 >= 98 && $group2 <= 99) {
                    return 'C. Hoare & Co.';
                }
                return 'The Royal Bank of Scotland';
            case '16':
                if ($group2 === '00' && $group3 === '38') {
                    return 'Drummonds Bank';
                } elseif ($group2 === '52' && $group3 === '21') {
                    return 'Cumberland Building Society';
                } elseif ($group2 === '57' && $group3 === '10') {
                    return 'Cater Allen Private Bank';
                }
                return 'The Royal Bank of Scotland';
            case '17':
            case '18':
                return 'The Royal Bank of Scotland';
            case '20':
            case '23':
            case '30':
            case '31':
            case '32':
            case '33':
            case '34':
            case '35':
            case '36':
            case '37':
            case '38':
            case '39':
            case '40':
            case '41':
            case '42':
            case '43':
            case '44':
            case '45':
            case '46':
            case '47':
            case '48':
            case '49':
            case '50':
            case '51':
            case '52':
            case '53':
            case '54':
            case '55':
            case '56':
            case '57':
            case '58':
            case '59':
            case '60':
            case '61':
            case '62':
            case '63':
            case '64':
            case '65':
            case '66':
            case '70':
            case '71':
            case '72':
            case '77':
            case '80':
            case '81':
            case '82':
            case '83':
            case '84':
            case '86':
            case '87':
            case '89':
            case '90':
            case '91':
            case '92':
            case '93':
            case '94':
            case '95':
            case '98':
            case '99':
                break;
        }
    }
}