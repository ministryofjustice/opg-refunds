<?php
namespace App\Service\Refund;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException;

class ProcessApplication
{

    private $notifyClient;
    private $dataHandler;

    public function __construct(NotifyClient $notifyClient, Data\DataHandlerInterface $dataHandler)
    {
        $this->notifyClient = $notifyClient;
        $this->dataHandler = $dataHandler;
    }

    public function process(array $data) : string
    {

        // Remove metadata
        unset($data['meta']);

        // Include the date submitted
        $data['submitted'] = gmdate(\DateTime::ISO8601);

        $reference = $this->dataHandler->store($data);

        $name = implode(' ', $data['donor']['name']);
        $contact = $data['contact'];

        /*
            The logic is:
                Only email entered - We send them just an email
                Only mobile number entered - We send them just an SMS
                Email and landline entered - We send them just an email
                Email and mobile entered - We send them an email and SMS
                Only landline number entered - We don't send a notification.
         */

        try {
            /*
             * If an email address was set, we always send them oan email.
             */
            if (isset($contact['email']) && !empty($contact['email'])) {
                // Send email...
                $this->notifyClient->sendEmail($contact['email'], '45e51dad-9269-4b77-816d-77202514c5e9', [
                    'ref' => IdentFormatter::format($reference),
                    'processed-by' => date('j F Y', strtotime($data['expected'])),
                    'donor-name' => $name,
                ]);
            }
        } catch (ApiException $e) {
        }

        //---

        try {
            /*
             * If a mobile number was entered, we send a SMS message.
             */
            if (isset($contact['phone']) && !empty($contact['phone'])) {
                // 070 numbers are personal, non-mobile, numbers.
                $isMobile = (bool)preg_match('/^07/', $contact['phone']) && !preg_match('/^070/', $contact['phone']);

                if ($isMobile) {
                    // Send SMS...
                    $this->notifyClient->sendSms($contact['phone'], 'dfa0cd3c-fcd5-431d-a380-3e4aa420e630', [
                        'ref' => IdentFormatter::format($reference),
                    ]);
                }
            }
        } catch (ApiException $e) {
        }

        //---

        return $reference;
    }
}
