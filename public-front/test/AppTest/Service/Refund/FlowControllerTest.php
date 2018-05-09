<?php
namespace AppTest\Service\Refund;

use PHPUnit\Framework\TestCase;

use App\Service\Session\Session;
use App\Service\Refund\FlowController;

class FlowControllerTest extends TestCase
{

    private function getTestSession()
    {
        return new Session([
            'applicant' => 'attorney',
            'deceased' => array(),
            'donor' => [
                'current' => array()
            ],
            'attorney' => array(),
            'case-number' => [
                'poa-case-number' => 'string'
            ],
            'postcodes' => array(),
            'contact' => array(),
            'account' => array()
        ]);
    }

    //-----------------------------------------------------------------------------------------
    // Test access controls

    private function accessTestHelper(string $route, string $needsData)
    {
        $data = $this->getTestSession();

        // Should be true with full state.
        $this->assertTrue(FlowController::routeAccessible($route, $data));

        //---

        $data = $this->getTestSession();
        unset($data[$needsData]);

        // Should be true with full state.
        $this->assertFalse(FlowController::routeAccessible($route, $data));
    }

    //------------------

    public function testCanAccessSummary()
    {
        $this->accessTestHelper('apply.summary', 'account');
    }

    public function testCanAccessAccountDetails()
    {
        $this->accessTestHelper('apply.account', 'contact');
    }

    public function testCanAccessContactDetails()
    {
        $this->accessTestHelper('apply.contact', 'case-number');
    }

    public function testCanAccessPostcode()
    {
        $this->accessTestHelper('apply.postcode', 'case-number');

        // Special case for psotcode page

        $data = $this->getTestSession();
        unset($data['case-number']['poa-case-number']);

        // Should be true with full state.
        $this->assertTrue(FlowController::routeAccessible('apply.postcode', $data));
    }

    public function testCanAccessCaseNumber()
    {
        $this->accessTestHelper('apply.case', 'attorney');
    }

    public function testCanAccessAttorney()
    {
        $this->accessTestHelper('apply.attorney', 'donor');
    }

    public function testCanAccessDonor()
    {
        $this->accessTestHelper('apply.donor', 'applicant');
    }

    public function testCanAccessDeceased()
    {
        $this->accessTestHelper('apply.deceased', 'applicant');
    }

    //-----------------------------------------------------------------------------------------
    // Test getNextRouteName() returns as expected based on a passed state (in the session).

    public function testSummary()
    {
        $data = $this->getTestSession();

        $this->assertEquals('apply.summary', FlowController::getNextRouteName($data));
    }

    public function testAccount()
    {
        $data = $this->getTestSession();

        unset($data['account']);

        $this->assertEquals('apply.account', FlowController::getNextRouteName($data));
    }

    public function testContact()
    {
        $data = $this->getTestSession();

        unset($data['contact']);

        $this->assertEquals('apply.contact', FlowController::getNextRouteName($data));
    }

    public function testPostcode()
    {
        $data = $this->getTestSession();

        unset($data['postcodes']);

        $this->assertEquals('apply.postcode', FlowController::getNextRouteName($data));
    }

    public function testCaseNumber()
    {
        $data = $this->getTestSession();

        unset($data['case-number']);

        $this->assertEquals('apply.case', FlowController::getNextRouteName($data));
    }

    public function testAttorney()
    {
        $data = $this->getTestSession();

        unset($data['attorney']);

        $this->assertEquals('apply.attorney', FlowController::getNextRouteName($data));
    }

    public function testDonor()
    {
        $data = $this->getTestSession();

        unset($data['donor']);

        $this->assertEquals('apply.donor', FlowController::getNextRouteName($data));
    }

    public function testDeceased()
    {
        $data = $this->getTestSession();

        unset($data['deceased']);

        $this->assertEquals('apply.deceased', FlowController::getNextRouteName($data));
    }

    public function testApplicant()
    {
        $data = $this->getTestSession();

        unset($data['applicant']);

        $this->assertEquals('apply.who', FlowController::getNextRouteName($data));
    }

    public function testDone()
    {
        $data = $this->getTestSession();

        $data['reference'] = '1234';

        $this->assertEquals('apply.done', FlowController::getNextRouteName($data));
    }

}