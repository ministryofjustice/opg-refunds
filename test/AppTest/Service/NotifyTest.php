<?php

namespace AppTest\Service;

use Alphagov\Notifications\Client as NotifyClient;
use App\Service\Claim as ClaimService;
use App\Service\Notify as NotifyService;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class NotifyTest extends MockeryTestCase
{

    const LONG_NAME = 'Miss xIyucniUes50SK1YsUxfvPfoU52XZ40xX5dN9ivlIlNrAneN6cu0t8cTUNJrvvRDcAUJ6yi2YYNyEre3cElJjV1OnzWyX4PlaXO9Nt9NScSwYFGh3w6kAOepl3qDUTve94uxVoycXXP6oAzSki1fB95iuYa5go4Efhhu9EXefYVrCTbolxlP3zP4A8dmegCKidhYYGgkKKsS0NNNjey29jaPcEpYEXoFixXePmZyqe3hweWXmsRqhYEhkxfIET7ZcPseYSzkKZ4aubCa3j25bdXEYyUXGTALk1proqDeWGVOMPbsBEzvYJU6Il6e2dWMlwfC02Ywms2UicyMugJTn7ng9zf7gMrXjBINWi2VZBJFdu1J49pbGsuo7rL8NJVFdNcdFwN81Xonno79gGie9KdLHbzQtwVBn8CWuNAA4mF35nVN1AB8wp1ovLxX4aE274LtqDAjzsQeNVMUiyYWrkrhvtoH0aUk27VgKuDmFb9bWTxD6mqM';

    /**
     * @var NotifyService
     */
    private $service;

    /**
     * @var MockInterface|EntityManager
     */
    private $entityManager;

    /**
     * @var MockInterface|NotifyClient
     */
    private $notifyClient;

    /**
     * @var MockInterface|ClaimService
     */
    private $claimService;

    protected function setUp()
    {
        $this->entityManager = Mockery::mock(EntityManager::class);
        $this->notifyClient = Mockery::mock(NotifyClient::class);
        $this->claimService = Mockery::mock(ClaimService::class);

        $this->service = new NotifyService($this->entityManager, $this->notifyClient, $this->claimService);
    }

    /**
     * @dataProvider donorNameForTemplateProvider
     *
     * @param string $templateId
     * @param string $donorName
     * @param string $expected
     */
    public function testGetDonorNameForTemplate(string $templateId, string $donorName, string $expected)
    {
        $result = $this->service->getDonorNameForTemplate($templateId, $donorName);
        $this->assertEquals($expected, $result);
    }

    public function donorNameForTemplateProvider()
    {
        return [
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_DUPLICATE_CLAIM, self::LONG_NAME, self::LONG_NAME],
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED, self::LONG_NAME, self::LONG_NAME ],
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_CHEQUE, self::LONG_NAME, self::LONG_NAME ],
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_REJECTION, self::LONG_NAME, self::LONG_NAME ],

            [NotifyService::NOTIFY_TEMPLATE_SMS_DUPLICATE_CLAIM, self::LONG_NAME, substr(self::LONG_NAME, 0, 126-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED, self::LONG_NAME, substr(self::LONG_NAME, 0, 175-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_CHEQUE, self::LONG_NAME, substr(self::LONG_NAME, 0, 175-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_NO_ELIGIBLE_POAS_FOUND, self::LONG_NAME, substr(self::LONG_NAME, 0, 71-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_PREVIOUSLY_REFUNDED, self::LONG_NAME, substr(self::LONG_NAME, 0, 201-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_NO_FEES_PAID, self::LONG_NAME, substr(self::LONG_NAME, 0, 175-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED, self::LONG_NAME, substr(self::LONG_NAME, 0, 124-1) ],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED, 'Mr Test Donor', 'Mr Test Donor'],
        ];
    }
}