<?php

namespace AppTest\DataModel;

use App\DataModel\Applications\Application;
use DateTime;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testDeserializeJson()
    {
        $applicationJson = '{"account":{"name":"Mr Test Attorney","hash":"099bad7153b0ab31f3b72ab532ba6cd2a7ef3f57ed773ee45e87dd8f6631c1a34cd4c06ad2c5a90698ac2c39eefec8e649568a3128e7f1ba994cfed0c0408189","details":"gXAZrIlxZ40h+fWOe0TEoNhtXaI6EB4oEE9uuvvrWyhdYjT84UIkp8fGp7J2LbmTw7Yo6vBNzo7IwlXOHE\/8CQtXDWDkUaBN9BjPz7m+xgwXH2kBtjAQKXv6l7hegEg9DG9QBuRLXzGP3GQTJKVKnhHUoFEgjiXDcyW7BDHApSHSmc8KaB3ByBHSYKASU8Af0epngZGg7yIpPIfgd+47Rd9TPKs5XwN+QqMAPpZ7CvwEC\/VR0LZmasmotWEjEG8TzyvexXG67DmJjzPe65nIuoj3TpuFQF5AqbR2JY+jsgKhO78YeZxLOgy+CVS9Nhk6kTyK9E7bdYZ8cZbg8g\/3CPuG3m2Wf0LBj+HUuUdcB3xR72ksgaXaFElvtg7aOzW9NoCKz1Czwom9BGrQhHqp2hTzQTZyKdAfMdnXAoM66O7XvEFED93cXGykbpZ20ZUgbwFyKFPwefGmdVGxXeXbvZAqGocQbzCCALANUgYdvaoNPSRoIIkvUYArj4IvZJCWXUUe0D8PdPre1sHbVzDF5UDxCalnfbRtr6blFZ+rJLML+ZOU5kO6cZEPARpwvP6zoro3LHJnr840QUla4OjsYuxt+0x8M6z11ti\/DGo\/dd+TBX3YK11\/ITgKYW+SjaD+anBA1OYpE8E1G9ViPmvwsf56yiFGCQad9jD+1ncgPAM="},"applicant":"attorney","attorney":{"name":{"title":"Mrs","first":"Test","last":"Donor 3"},"dob":"1975-04-03"},"contact":{"email":"test@attorney03.com","mobile":"387523985325"},"donor":{"name":{"title":"Mr","first":"Test","last":"Attorney 03"},"dob":"1973-02-01"},"verification":{"case-number":"3247842987","donor-postcode":"B26 2LW","attorney-postcode":"B26 2LW"},"expected":"2017-12-04","submitted":"2017-09-11T11:05:10+0000"}';

        $application = new Application($applicationJson);

        $this->assertEquals('attorney', $application->getApplicant());

        $this->assertNotNull($application->getDonor());
        $this->assertNotNull($application->getDonor()->getName());
        $this->assertEquals('Mr', $application->getDonor()->getName()->getTitle());
        $this->assertEquals('Test', $application->getDonor()->getName()->getFirst());
        $this->assertEquals('Attorney 03', $application->getDonor()->getName()->getLast());
        $this->assertEquals(new DateTime('1973-02-01'), $application->getDonor()->getDob());

        $this->assertNotNull($application->getAttorney());
        $this->assertNotNull($application->getAttorney()->getName());
        $this->assertEquals('Mrs', $application->getAttorney()->getName()->getTitle());
        $this->assertEquals('Test', $application->getAttorney()->getName()->getFirst());
        $this->assertEquals('Donor 3', $application->getAttorney()->getName()->getLast());
        $this->assertEquals(new DateTime('1975-04-03'), $application->getAttorney()->getDob());

        $this->assertNotNull($application->getContact());
        $this->assertEquals('test@attorney03.com', $application->getContact()->getEmail());
        $this->assertEquals('387523985325', $application->getContact()->getMobile());

        $this->assertNotNull($application->getVerification());
        $this->assertEquals('3247842987', $application->getVerification()->getCaseNumber());
        $this->assertEquals('B26 2LW', $application->getVerification()->getDonorPostcode());
        $this->assertEquals('B26 2LW', $application->getVerification()->getAttorneyPostcode());

        $this->assertNotNull($application->getAccount());
        $this->assertEquals('Mr Test Attorney', $application->getAccount()->getName());
        $this->assertNull($application->getAccount()->getAccountNumber());
        $this->assertNull($application->getAccount()->getSortCode());

        $this->assertEquals(new DateTime('2017-09-11T11:05:10+0000'), $application->getSubmitted());
        $this->assertEquals(new DateTime('2017-12-04'), $application->getExpected());
    }

    public function testDeserializeArray()
    {
        $applicationArray = array (
            'account' =>
                array (
                    'name' => 'Mr Test Donor',
                    'hash' => '06e1e19a23e7799c2d4e12e92e4ddbff495bedd32e431786284b3313ef46221da057f025b5b9b7e0e7ca0e1ed31663f688d5d079b45d88b534ab5029a7471847',
                    'details' => 'nFAIhJHBhUHA739CzgJ4WhH1x0EhVzQpxNROLr5LP/E9He8Wvmib8NivLA4sFL29iALNSaSszyr2IVow1F+oeuhmBFw+oad+8DXPn/QZ5JiLTPmJACq6kLD9pYbfjNG/FN98ILejgPMKuIFnDY9kkwR4U+oueP4/2rL7rrX/NrHrj+k8z2YWd3fDU6TnOz8782zobvEDP/Caw5icfagAKNg1dRwOYAs4y4ZpUPEbfR21AhxCmvDW61Ggrc9k/dV17/os4BWNBvdBo08/HGsbYYJQ6pwoivTsPPAfgRPL/GVMtJg8HQpLWtujh5iAmaPn6iDdeoWcmNv/Qe36o7+585ORFt9/GeVmmDcQMDphcVQQBu+8WDawllXKc9GfHHz7Z2B4I36BeMJW4dDGYoejGn0v1S9PKvC43++Ahd4slAfQGASua+gACVhA/DYGrO2wQ0UJ5CH+9qliPStsN3SignU+zzeJtrNCptD/RfiErPheFxij9rCY5rm944u0hfMBUFcsXVfqbjSPpb2GulSe93o47CzqVsqjktVOsLReasv8MItgiWrwMCmGpx9sgWrH2e58qSojy2LIqdq9SJEbbPk0m7En+uf2/0nvVIHQbMcerZGM3p3FEJ6tqqkrXC1pGmdPINukpTbTXqiEIRMDPk386vaR7rXH9OxL9XK1puM=',
                ),
            'applicant' => 'donor',
            'attorney' =>
                array (
                    'name' =>
                        array (
                            'title' => 'Mrs',
                            'first' => 'Test',
                            'last' => 'Attorney 01',
                        ),
                    'dob' => '1970-04-03',
                ),
            'contact' =>
                array (
                    'email' => 'test@donor.com',
                    'mobile' => '0123456789',
                ),
            'donor' =>
                array (
                    'name' =>
                        array (
                            'title' => 'Mr',
                            'first' => 'Test',
                            'last' => 'Donor 01',
                        ),
                    'dob' => '1970-02-01',
                ),
            'verification' =>
                array (
                    'case-number' => '3247842368',
                    'donor-postcode' => 'WS14 9UN',
                    'attorney-postcode' => 'WS14 9UN',
                ),
            'expected' => '2017-12-04',
            'submitted' => '2017-09-11T09:54:13+0000',
        );

        $application = new Application($applicationArray);

        $this->assertEquals('donor', $application->getApplicant());

        $this->assertNotNull($application->getDonor());
        $this->assertNotNull($application->getDonor()->getName());
        $this->assertEquals('Mr', $application->getDonor()->getName()->getTitle());
        $this->assertEquals('Test', $application->getDonor()->getName()->getFirst());
        $this->assertEquals('Donor 01', $application->getDonor()->getName()->getLast());
        $this->assertEquals(new DateTime('1970-02-01'), $application->getDonor()->getDob());

        $this->assertNotNull($application->getAttorney());
        $this->assertNotNull($application->getAttorney()->getName());
        $this->assertEquals('Mrs', $application->getAttorney()->getName()->getTitle());
        $this->assertEquals('Test', $application->getAttorney()->getName()->getFirst());
        $this->assertEquals('Attorney 01', $application->getAttorney()->getName()->getLast());
        $this->assertEquals(new DateTime('1970-04-03'), $application->getAttorney()->getDob());

        $this->assertNotNull($application->getContact());
        $this->assertEquals('test@donor.com', $application->getContact()->getEmail());
        $this->assertEquals('0123456789', $application->getContact()->getMobile());

        $this->assertNotNull($application->getVerification());
        $this->assertEquals('3247842368', $application->getVerification()->getCaseNumber());
        $this->assertEquals('WS14 9UN', $application->getVerification()->getDonorPostcode());
        $this->assertEquals('WS14 9UN', $application->getVerification()->getAttorneyPostcode());

        $this->assertNotNull($application->getAccount());
        $this->assertEquals('Mr Test Donor', $application->getAccount()->getName());
        $this->assertNull($application->getAccount()->getAccountNumber());
        $this->assertNull($application->getAccount()->getSortCode());

        $this->assertEquals(new DateTime('2017-09-11T09:54:13+0000'), $application->getSubmitted());
        $this->assertEquals(new DateTime('2017-12-04'), $application->getExpected());
    }
}