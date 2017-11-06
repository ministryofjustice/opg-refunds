<?php

namespace Ingestion\Service;

use App\Entity\Cases\Claim;
use App\Entity\Cases\Note;
use Ingestion\Entity\Application;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ApplicationIngestion
{
    /**
     * @var EntityManager
     */
    private $applicationsEntityManager;

    /**
     * @var EntityRepository
     */
    private $applicationRepository;

    /**
     * @var EntityManager
     */
    private $casesEntityManager;

    /**
     * @var EntityRepository
     */
    private $caseRepository;


    /**
     * DataMigration constructor.
     * @param EntityManager $applicationsEntityManager
     */
    public function __construct(EntityManager $applicationsEntityManager, EntityManager $casesEntityManager)
    {
        $this->applicationsEntityManager = $applicationsEntityManager;
        $this->applicationRepository = $this->applicationsEntityManager->getRepository(Application::class);
        $this->casesEntityManager = $casesEntityManager;
        $this->caseRepository = $this->casesEntityManager->getRepository(Claim::class);
    }

    /**
     * @return Application
     */
    public function getNextApplication()
    {
        /** @var Application $application */
        $application = $this->applicationRepository->findOneBy(['processed' => false], ['created' => 'ASC']);
        return $application;
    }

    /**
     * Decompress data from Public Front.
     *
     * @param Application $application
     * @return string Application JSON
     */
    public function getDecompressedData(Application $application): string
    {
        try{
            return gzinflate($application->getData());
        } catch ( \Throwable $e)
        {
            /*
             * Data used to be encrypted when it reached this point, but that's no longer the case.
             * If decompression did fail, it's possible this function was trying to process legacy data.
             * In reality, this should never be the case.
             */
            throw new \UnexpectedValueException('Unable to decompress payload. Maybe it was encrypted?');
        }
    }

    /**
     * @param Application $application
     * @return array
     */
    public function getApplicationData(Application $application): array
    {
        $uncompressedData = $this->getDecompressedData($application);
        $applicationData = json_decode($uncompressedData, true);
        return $applicationData;
    }

    /**
     * @param Application $application
     * @return Claim
     */
    public function getClaim(Application $application): Claim
    {
        $uncompressedData = $this->getDecompressedData($application);
        $applicationData = json_decode($uncompressedData, true);
        $donorName = "{$applicationData['donor']['current']['name']['title']} {$applicationData['donor']['current']['name']['first']} {$applicationData['donor']['current']['name']['last']}";
        $claim = new Claim($application->getId(), $application->getCreated(), $applicationData, $donorName, $applicationData['account']['hash']);
        return $claim;
    }

    public function ingestAllApplication()
    {
        $count = 0;

        while ($this->ingestApplication()) {
            $count++;
        }

        return $count;
    }

    /**
     * Ingest a single application and copy it to the cases database
     *
     * @return bool true if ingestion was successful
     */
    public function ingestApplication(): bool
    {
        $application = $this->getNextApplication();
        if ($application !== null) {
            $claim = $this->getClaim($application);
            $this->casesEntityManager->persist($claim);

            if ($this->caseRepository->findOneBy(['id' => $claim->getId()]) === null) {
                try {
                    $applicationData = $this->getApplicationData($application);

                    $applicantName = '';
                    if ($applicationData['applicant'] === 'donor') {
                        $applicantName = $claim->getDonorName() . ' (Donor)';
                    } elseif ($applicationData['applicant'] === 'attorney') {
                        $applicantName = "{$applicationData['attorney']['current']['name']['title']} {$applicationData['attorney']['current']['name']['first']} {$applicationData['attorney']['current']['name']['last']} (Attorney)";
                    }

                    $receivedDateString = date('d M Y \a\t H:i', $claim->getReceivedDateTime()->getTimestamp());
                    $note = new Note('Claim submitted', "Claim submitted by $applicantName on $receivedDateString", $claim);

                    $this->casesEntityManager->persist($note);
                    $this->casesEntityManager->flush();

                    $this->setProcessed($application);

                } catch (UniqueConstraintViolationException $ex) {
                    $this->setProcessed($application);
                }
            } else {
                $this->setProcessed($application);
            }

            return true;
        }

        return false;
    }

    /**
     * @param Application $application
     */
    private function setProcessed(Application $application)
    {
        //Null data to make sure no data is left in database potentially accessible on the public internet
        $application->setData(null);
        $application->setProcessed(true);
        $this->applicationsEntityManager->persist($application);
        $this->applicationsEntityManager->flush();
    }
}