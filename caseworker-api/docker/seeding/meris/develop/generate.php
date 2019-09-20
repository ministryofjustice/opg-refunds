<?php
/**
 * Generates Meris test data
 */

$randomDonors = file_get_contents('https://randomuser.me/api/?results=2000&nat=gb&seed=md');
$randomDonors = json_decode($randomDonors, true);

$randomAttorneys = file_get_contents('https://randomuser.me/api/?results=5000&nat=gb&seed=ma');
$randomAttorneys = json_decode($randomAttorneys, true);

$randomAttorneys = $randomAttorneys['results'];

$nextAtt = 0;
$result = array();

foreach($randomDonors['results'] as $i => $user){

    //"date-of-receipt":"17\/03\/2015",
    //"donor-title":"mr",
    //"donor-forename-s":"laurence john",
    //"donor-lastname":"pyzer",
    //"donor-dob":"05\/08\/1945",
    //"donor-postcode":"ha7 4be",

    // A one in 5 chance of this being the next in a sequence
    if( random_int(1,5) % 5 == 0 && $i > 0 ){

        $result[$i] = $result[$i - 1];
        $result[$i]['sequence']++;

        continue;
    }

    $attorney = $randomAttorneys[$nextAtt];
    $nextAtt++;
    if( $nextAtt > count($randomAttorneys) ){ $nextAtt = 0; }

    $result[$i] = [
        'id' => random_int(1000000, 9999999),
        'sequence' => 1,
        'data' => [
            'donor-title' => strtolower($user['name']['title']),
            'donor-forename' => strtolower($user['name']['first']),
            'donor-lastname' => strtolower($user['name']['last']),
            'donor-dob' => date('Y-m-d', strtotime($user['dob'])),
            'donor-postcode' => strtolower(preg_replace('/\s+/', '', $user['location']['postcode'])),
            'date-of-receipt' => date('Y-m-d', random_int(1359504000, 1515409274)),
            'attorneys' => array([
                'attorney-name' => strtolower("{$attorney['name']['title']} {$attorney['name']['first']} {$attorney['name']['last']}"),
                'attorney-dob' => date('Y-m-d', strtotime($attorney['dob'])),
                'attorney-postcode' => strtolower(preg_replace('/\s+/', '', $attorney['location']['postcode'])),
            ]),
        ],
    ];

    while( random_int(1,3) % 3 == 0 ){

        $attorney = $randomAttorneys[$nextAtt];
        $nextAtt++;
        if( $nextAtt > count($randomAttorneys) ){ $nextAtt = 0; }

        $result[$i]['data']['attorneys'][] = [
            'attorney-name' => strtolower("{$attorney['name']['title']} {$attorney['name']['first']} {$attorney['name']['last']}"),
            'attorney-dob' => date('Y-m-d', strtotime($attorney['dob'])),
            'attorney-postcode' => strtolower(preg_replace('/\s+/', '', $attorney['location']['postcode'])),
        ];
    }

}

$sql = fopen("data.sql","w");
$txt = fopen("data.txt","w");

fwrite($sql, "BEGIN;\n");

foreach ($result as $o) {

    $json = json_encode($o['data'], JSON_HEX_APOS+JSON_HEX_QUOT);
    $line = "INSERT INTO meris VALUES ({$o['id']}, {$o['sequence']}, '{$json}');";
    fwrite($sql, "{$line}\n");

    $json = json_encode($o, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
    fwrite($txt, "{$json}\n--------------------------------------------------------\n");

}

fwrite($sql, "COMMIT;\n");

fclose($txt);
fclose($sql);
