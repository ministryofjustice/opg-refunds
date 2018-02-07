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
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_DUPLICATE_CLAIM, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150'],
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150'],
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_CLAIM_APPROVED_CHEQUE, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150'],
            [NotifyService::NOTIFY_TEMPLATE_EMAIL_REJECTION, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150'],

            [NotifyService::NOTIFY_TEMPLATE_SMS_DUPLICATE_CLAIM, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 1'],
            [NotifyService::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters'],
            [NotifyService::NOTIFY_TEMPLATE_SMS_CLAIM_APPROVED_CHEQUE, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 C'],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_NO_ELIGIBLE_POAS_FOUND, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters M'],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_PREVIOUSLY_REFUNDED, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Cha'],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_NO_FEES_PAID, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 '],
            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED, 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 150', 'Mr Test 150 Characters Mr Test 150 Characters Mr Test 150 Characters Mr Test 15'],

            [NotifyService::NOTIFY_TEMPLATE_SMS_REJECTION_CLAIM_NOT_VERIFIED, 'Mr Test Donor', 'Mr Test Donor'],
        ];
    }
}