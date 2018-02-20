<?php

namespace App\Service;

use Opg\Refunds\Log\Initializer;

class Account implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

    /**
     * @var array
     */
    private static $buildingSocietyHashes = null;

    /**
     * @var string
     */
    private $sourceFolder;

    /**
     * @var string
     */
    private $salt;

    public function __construct(string $sourceFolder, string $salt)
    {
        $this->sourceFolder = $sourceFolder;
        $this->salt = $salt;

        if (substr($this->sourceFolder, -1) !== '/') {
            $this->sourceFolder .= '/';
        }
    }

    public function getBuildingSocietyHashes()
    {
        if (self::$buildingSocietyHashes === null) {
            $buildingSocietyCsvFilename = $this->sourceFolder . 'building_society.csv';

            $this->getLogger()->info("Loading Building Society details from {$buildingSocietyCsvFilename}");

            $buildingSocietyHashes = [];

            $buildingSocietyCsvHandle = fopen($buildingSocietyCsvFilename, "r");
            if ($buildingSocietyCsvHandle) {
                while (($line = fgets($buildingSocietyCsvHandle)) !== false) {
                    $account = explode(',', $line);

                    $accountDetails = json_encode([
                        'sort-code' => substr($account[1], 0, 6),
                        'account-number' => substr($account[2], 0, 8),
                    ]);

                    $hash = hash('sha512', $this->salt . $accountDetails);

                    $buildingSocietyHashes[$hash] = $account[0];

                    $this->getLogger()->debug("Added Building Society details {$account[0]} {$accountDetails} {$hash}");
                }

                fclose($buildingSocietyCsvHandle);
            } else {
                throw new \Exception("Failed to open building society CSV file {$buildingSocietyCsvFilename}");
            }

            self::$buildingSocietyHashes = $buildingSocietyHashes;

            $this->getLogger()->info('Successfully loaded ' . count($buildingSocietyHashes) . ' Building Society details from');
        }

        return self::$buildingSocietyHashes;
    }

    public function isBuildingSociety(string $accountHash): bool
    {
        return array_key_exists($accountHash, $this->getBuildingSocietyHashes());
    }

    public function getBuildingSocietyName(string $accountHash)
    {
        if ($this->isBuildingSociety($accountHash)) {
            $name = $this->getBuildingSocietyHashes()[$accountHash];
            if (strpos($name, 'Building Society') === false) {
                $name .= ' (Building Society)';
            }
            return $name;
        }

        return null;
    }
}