<?php

namespace Ingestion\Worker;

use Exception;
use Ingestion\Service\ApplicationIngestion;
use Opg\Refunds\Log\Initializer;

/**
 * Class IngestionWorker
 * @package Ingestion\Worker
 */
class IngestionWorker implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

    /**
     * @var bool Should the worker continue to run. Setting this to false will terminate the worker.
     */
    private $run = true;

    /**
     * @var int Tracks consecutive exceptions
     */
    private $exceptionCount = 0;

    /**
     * @var ApplicationIngestion
     */
    private $applicationIngestion;

    public function __construct(ApplicationIngestion $applicationIngestion)
    {
        $this->applicationIngestion = $applicationIngestion;
    }

    /**
     * Stops the worker ASAP (it will finished any started jobs)
     */
    public function stop()
    {
        $this->getLogger()->notice("Stopping worker");
        $this->run = false;
    }

    /**
     * Starts the worker. This process will run until stop is called
     *
     * @return bool
     */
    public function run()
    {
        $this->getLogger()->notice("Starting worker");

        $this->run = true;

        // This loop will run until the Worker is terminated
        while ($this->run) {
            try {
                if ($this->applicationIngestion->ingestApplication()) {
                    $this->getLogger()->debug('Successfully ingested one application. Trying again immediately');
                } else {
                    // Sleep for a random amount of time to prevent multiple workers synchronising
                    $sleepSeconds = rand(1, 30);
                    $this->getLogger()->debug("Sleeping for {$sleepSeconds} seconds");

                    //Calls signal handlers for pending signals. Ensures run property is up to date
                    pcntl_signal_dispatch();
                    if ($this->run) {
                        sleep($sleepSeconds);
                    }
                }

                $this->exceptionCount = 0;
            } catch (Exception $ex) {
                $this->exceptionCount++;

                // Any exception implies a significant problem
                if ($this->exceptionCount < 3) {
                    // Initially warn about the exception
                    $this->getLogger()->warn(
                        'Exception when ingesting application. ' . $ex->getMessage(),
                        ['exception' => $ex]
                    );

                    //Calls signal handlers for pending signals. Ensures run property is up to date
                    pcntl_signal_dispatch();
                    if ($this->run) {
                        // Wait and try again in case this is a temporary issue
                        sleep(30);
                    }
                } else {
                    // Issue is now critical so log as such and shut down
                    $this->getLogger()->crit(
                        'Exception when ingesting application. ' . $ex->getMessage(),
                        ['exception' => $ex]
                    );

                    $this->getLogger()->warn("Worker ended unsuccessfully");

                    // Return run loop ended unsuccessfully
                    return false;
                }
            }

            //Calls signal handlers for pending signals. Ensures run property is up to date
            pcntl_signal_dispatch();
        }

        $this->getLogger()->notice("Worker ended successfully");

        // Return run loop ended successfully
        return true;
    }
}
