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

        //Logic from https://en.wikipedia.org/wiki/Sort_code#List_of_sort_codes_of_the_United_Kingdom
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
            case '21':
            case '22':
            case '23':
                if ($group2 === '05' && $group3 === '80') {
                    return 'Metro Bank';
                } elseif ($group2 === '14' && $group3 === '70') {
                    return 'TransferWise';
                } elseif ($group2 === '22' && $group3 === '21') {
                    return 'Fire Financial Services';
                } elseif ($group2 === '32' && $group3 === '72') {
                    return 'Pockit';
                } elseif ($group2 === '69' && $group3 === '72') {
                    return 'Prepay Technologies';
                }
                return 'Barclays Bank';
            case '24':
            case '25':
            case '26':
            case '27':
            case '28':
            case '29':
                return 'Barclays Bank';
            case '30':
                if ($group2 === '00' && $group3 === '66') {
                    return 'Arbuthnot Latham';
                }
                return 'Lloyds Bank';
            case '31':
            case '32':
            case '33':
            case '34':
            case '35':
            case '36':
            case '37':
            case '38':
            case '39':
                return 'Lloyds Bank';
            case '40':
                if ($group2 === '12' && $group3 >= 50 && $group3 < 55) {
                    return 'M&S Bank';
                } elseif ($group2 === '51' && $group3 === '98') {
                    return 'Turkish Bank UK';
                } elseif ($group2 === '60' && $group3 === '80') {
                    return 'CashFlows';
                } elseif ($group2 === '63' && $group3 === '01') {
                    return 'The Coventry Building Society';
                } elseif ($group2 === '63' && $group3 === '77') {
                    return 'Bank of Cyprus UK';
                } elseif ($group2 === '64' && $group3 === '25') {
                    return 'Virgin Money PLC';
                } elseif ($group2 === '65' && $group3 === '00') {
                    return 'Norwich & Peterborough Building Society';
                }
                return 'HSBC Bank';
            case '41':
            case '42':
            case '43':
            case '44':
            case '45':
            case '46':
            case '47':
            case '48':
            case '49':
                if ($group2 === '99' && $group3 >= 79) {
                    return 'Deutsche Bank';
                }
                return 'HSBC Bank';
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
                if ($group2 === '83' && $group3 === '12') {
                    return 'Atom Bank';
                } elseif ($group2 === '83' && $group3 === '66') {
                    return 'Fidor Bank UK';
                } elseif ($group2 === '83' && $group3 === '71') {
                    return 'Starling Bank';
                }
                return 'National Westminster Bank';
            case '61':
            case '62':
            case '63':
            case '64':
            case '65':
            case '66':
                return 'National Westminster Bank';
            //case '70':
            case '71':
                return 'National Savings Bank';
            case '72':
                return 'Santander UK';
            case '77':
                return 'TSB';
            case '80':
            case '81':
                return 'Bank of Scotland';
            case '82':
                return 'Clydesdale Bank';
            case '83':
            case '84':
            case '86':
                return 'The Royal Bank of Scotland';
            case '87':
                return 'TSB';
            case '89':
                return 'Santander UK';
            case '90':
                return 'Bank of Ireland';
            case '91':
                return 'Danske Bank';
            case '92':
                return 'Central Bank of Ireland';
            case '93':
                return 'Allied Irish Banks';
            case '94':
                return 'Bank of Ireland';
            case '95':
                return 'Danske Bank';
            case '98':
                return 'Ulster Bank';
            case '99':
                return 'Permanent TSB';
                break;
        }

        return '';
    }
}
