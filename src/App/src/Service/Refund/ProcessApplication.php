<?php
namespace App\Service\Refund;

use App\Service\Refund\Data\PhoneNumber;
use League\JsonGuard\Validator as JsonValidator;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException;

class ProcessApplication
{

    private $notifyClient;
    private $dataHandler;
    private $jsonSchemaPath;

    public function __construct(
        NotifyClient $notifyClient,
        Data\DataHandlerInterface $dataHandler,
        string $jsonSchemaPath
    ) {
        $this->notifyClient = $notifyClient;
        $this->dataHandler = $dataHandler;
        $this->jsonSchemaPath = $jsonSchemaPath;
    }

    public function process(array $data) : string
    {

        // Remove metadata
        unset($data['meta']);

        // Include the date submitted
        $data['submitted'] = gmdate(\DateTime::ISO8601);

        //---

        // Validate the generated JSON against our schema.
        $validator = new JsonValidator(
            json_decode(json_encode($data)),
            json_decode(file_get_contents($this->jsonSchemaPath))
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            throw new \UnexpectedValueException('Invalid JSON generated: ' . print_r($errors, true));
        }

        //---

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
                    'claim-code' => IdentFormatter::format($reference),
                    'processed-by-date' => date('j F Y', strtotime($data['expected'])),
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
                $phone = new PhoneNumber($contact['phone']);

                if ($phone->isMobile()) {
                    // Send SMS...
                    $this->notifyClient->sendSms($phone->get(), 'dfa0cd3c-fcd5-431d-a380-3e4aa420e630', [
                        'claim-code' => IdentFormatter::format($reference),
                        'processed-by-date' => date('j F Y', strtotime($data['expected'])),
                        'donor-name' => $name,
                    ]);
                }
            }
        } catch (ApiException $e) {
        }

        //---

        return $reference;
    }
}
