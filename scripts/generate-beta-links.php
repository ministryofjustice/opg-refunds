<?php

include_once('../vendor/autoload.php');

use Zend\Crypt\Key\Derivation\Pbkdf2;
use Zend\Math\BigInteger\BigInteger;

//-----------------------------------
// Get vars

$key = null;
$startFrom = null;
$numberToGenerate = null;
$expire = null;

foreach($argv as $i=>$argument){

    switch ($argument)
    {
        case '--hex-key':
            $key = $argv[$i+1];
            break;
        case '--start-from':
            $startFrom = $argv[$i+1];
            break;
        case '--number-to-generate':
            $numberToGenerate = $argv[$i+1];
            break;
        case '--expire':
            $expire = $argv[$i+1];
            break;
    }

}

// Check the key is found and is 256-bit
if(empty($key) || mb_strlen(hex2bin($key), '8bit') != (256/8) ){
    echo "Error: Key is missing or is not a 256bit hex value\n"; exit(1);
}

if(!is_numeric($startFrom) || $startFrom < 1 || (int)$startFrom != $startFrom){
    echo "Error: --start-from must be an integer > 0\n"; exit(1);
}

if(!is_numeric($numberToGenerate) || $numberToGenerate < 1 || (int)$numberToGenerate != $numberToGenerate){
    echo "Error: --number-to-generate must be an integer > 0\n"; exit(1);
}

$expire = strtotime("{$expire} 23:59:59 UTC");

if( !is_numeric($expire) || $expire < time() ){
    echo "Error: --expire must be a future date in the format yyyy-mm-dd\n"; exit(1);
}

//------------------------------------------

echo "Generating $numberToGenerate links, starting from ID $startFrom, which will be valid until ".gmdate('r', $expire)."\n";

for($i = $startFrom; $i < ($numberToGenerate+$startFrom); $i++){

    $iv = random_bytes(32);

    $hash = Pbkdf2::calc(
        'sha256',
        $key,
        $iv,
        5000,
        256 * 2
    );

    //---

    $details = "{$i}/{$expire}";
    $signature = hash_hmac('sha256', $details, $hash);

    echo '/beta/'.$details . '/' . hexTobase62(bin2hex($iv).$signature)."\n";

}

//------------------------------------------

function hexTobase62( $value ){
    return BigInteger::factory('bcmath')->baseConvert( $value, 16, 62 );
}
