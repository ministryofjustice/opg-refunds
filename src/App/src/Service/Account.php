<?php

namespace App\Service;

class Account
{
    /**
     * @var array
     */
    private $buildingSocietyHashes = [];

    public function __construct(string $sourceFolder, string $salt)
    {
        if (substr($sourceFolder, -1) !== '/') {
            $sourceFolder .= '/';
        }

        $buildingSocietyCsvFilename = $sourceFolder . 'building_society.csv';

        $buildingSocietyCsvHandle = fopen($buildingSocietyCsvFilename, "r");
        if ($buildingSocietyCsvHandle) {
            while (($line = fgets($buildingSocietyCsvHandle)) !== false) {
                $account = explode(',', $line);

                $accountDetails = json_encode([
                    'sort-code' => substr($account[1], 0, 6),
                    'account-number' => substr($account[2], 0, 8),
                ]);

                var_dump($accountDetails);

                $hash = hash('sha512', $salt . $accountDetails);

                $this->buildingSocietyHashes[$hash] = $account[0];
            }

            fclose($buildingSocietyCsvHandle);
        } else {
            throw new \Exception("Failed to open building society CSV file {$buildingSocietyCsvFilename}");
        }

        var_dump($this->buildingSocietyHashes);
    }
}