<?php

$ids = array();

//---

$contents = file('../../meris/develop/data.sql');
foreach($contents as $line) {

    if (strlen($line) < 26) {
        continue;
    }

    preg_match('/date-of-receipt\"\:\"([\d]{4}-[\d]{2}-[\d]{2})\"/', $line, $matches);

    if (!isset($matches[1])) {
        die("Error with line {$line}\n");
    }

    $ids[] = [
        'case-number' => substr($line, 26, 7),
        'sequence' => substr($line, 35, 1),
        'date' => $matches[1],
    ];
}

//---

$contents = file('../../sirius/develop/data.sql');
foreach($contents as $line) {

    if (strlen($line) < 26) {
        continue;
    }

    preg_match('/date-of-receipt\"\:\"([\d]{4}-[\d]{2}-[\d]{2})\"/', $line, $matches);

    if (!isset($matches[1])) {
        die("Error with line {$line}\n");
    }

    $ids[] = [
        'case-number' => substr($line, 27, 12),
        'sequence' => 1,
        'date' => $matches[1],
    ];
}

//----------------------

$sql = fopen("data.sql","w");
$txt = fopen("data.txt","w");

fwrite($sql, "BEGIN;\n");

foreach ($ids as $o) {

    $rand = random_int(0,100);

    if ($rand == 0) {
        // We don't have all the real data, so drop some to simulate this.
        echo "Skipping\n";
        continue;
    } elseif ($rand < 5) {
        $amount = 0;
    } elseif ($rand < 15) {
        $amount = 130;
    } elseif ($rand < 35) {
        $amount = 55;
    } elseif ($rand > 98) {
        // In the real data some dates are wrong.
        // Replicate that here.
        echo "Break date\n";
        $o['date'] = date('Y-m-d', random_int(1359504000, 1515409274));
    } else {
        $amount = 110;
    }

    $o['amount'] = $amount;

    $line = "INSERT INTO finance VALUES ({$o['case-number']}, {$o['sequence']}, {$o['amount']}, '{$o['date']}');";
    fwrite($sql, "{$line}\n");

    $json = json_encode($o, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
    fwrite($txt, "{$json}\n--------------------------------------------------------\n");

}

fwrite($sql, "COMMIT;\n");

fclose($txt);
fclose($sql);
