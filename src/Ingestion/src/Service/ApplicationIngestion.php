<?php

namespace Ingestion\Service;

use App\Crypt\Hybrid as HybridCipher;
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
     * @var HybridCipher
     */
    private $fullCipher;

    /**
     * DataMigration constructor.
     * @param EntityManager $applicationsEntityManager
     * @param HybridCipher $fullCipher
     */
    public function __construct(EntityManager $applicationsEntityManager, EntityManager $casesEntityManager, HybridCipher $fullCipher)
    {
        $this->applicationsEntityManager = $applicationsEntityManager;
        $this->applicationRepository = $this->applicationsEntityManager->getRepository(Application::class);
        $this->casesEntityManager = $casesEntityManager;
        $this->caseRepository = $this->casesEntityManager->getRepository(Claim::class);
        $this->fullCipher = $fullCipher;
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
     * @param Application $application
     * @return string Application JSON
     */
    public function getDecryptedData(Application $application): string
    {
        $json = null;

        try{
            $json = gzinflate($application->getData());
        } catch ( \Throwable $e)
        {
        }

        // If JSON is not a string, we'll assume the payload way encrypted.
        if (!is_string($json)) {
            $json = gzinflate($this->fullCipher->decrypt($application->getData()));
        }

        return $json;
    }

    /**
     * @param Application $application
     * @return array
     */
    public function getApplicationData(Application $application): array
    {
        $decryptedData = $this->getDecryptedData($application);
        $applicationData = json_decode($decryptedData, true);
        return $applicationData;
    }

    /**
     * @param Application $application
     * @return Claim
     */
    public function getClaim(Application $application): Claim
    {
        $decryptedData = $this->getDecryptedData($application);
        $applicationData = json_decode($decryptedData, true);
        $donorName = "{$applicationData['donor']['current']['name']['title']} {$applicationData['donor']['current']['name']['first']} {$applicationData['donor']['current']['name']['last']}";
        $claim = new Claim($application->getId(), $application->getCreated(), $decryptedData, $donorName, $applicationData['account']['hash']);
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