<?php
namespace App\Service\Refund;

use App\Service\Refund\Data\PhoneNumber;
use League\JsonGuard\Validator as JsonValidator;
use League\JsonReference\Dereferencer as JsonDereferencer;
use League\JsonReference\ReferenceSerializer\InlineReferenceSerializer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException;

use Opg\Refunds\Log\Initializer;

class ProcessApplication implements Initializer\LogSupportInterface
{
    use Initializer\LogSupportTrait;

    const MAX_SMS_DONOR_NAME_LENGTH = 164;

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

        // Remove unwanted data
        unset($data['meta']);
        unset($data['notes']);
        unset($data['case-number']['have-poa-case-number']);
        unset($data['postcodes']['postcode-options']);
        unset($data['donor']['poa']['different-name-on-poa']);

        //---

        // Tidy up data - strip out quote marks
        array_walk_recursive($data, function (&$item, $key) {
            $item = (is_string($item)) ? str_replace('"', '', $item) : $item;
        });

        //---

        // Include the date submitted
        $data['version'] = 1;


        $data['submitted'] = gmdate(\DateTime::ISO8601);

        //---

        $dereferencer = JsonDereferencer::draft6();
        $dereferencer->setReferenceSerializer(new InlineReferenceSerializer());

        $schema = $dereferencer->dereference(json_decode(file_get_contents($this->jsonSchemaPath)));

        // Validate the generated JSON against our schema.
        $validator = new JsonValidator(
            json_decode(json_encode($data)), // Simplest way to convert to stdClass
            $schema
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            throw new \UnexpectedValueException('Invalid JSON generated: ' . print_r($errors, true));
        }

        //---

        $reference = $this->dataHandler->store($data);

        //---

        $this->getLogger()->info('Application submitted', [ 'claim-code' => $reference ]);

        //---

        $name = implode(' ', $data['donor']['current']['name']);
        $contact = $data['contact'];

        // If we are sending notifications...
        if ($contact['receive-notifications']) {
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
                        'donor-dob' => date('j F Y', strtotime($data['donor']['current']['dob']))
                    ]);
                }
            } catch (ApiException $e) {
                $this->getLogger()->alert(
                    'Unable to send email via Notify',
                    [
                        'exception' => $e->getMessage(),
                        'notify-message' => (string)$e->getResponse()->getBody()
                    ]
                );
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
                            'donor-name' => $this->getDonorNameForSms($name),
                            'donor-dob' => date('j F Y', strtotime($data['donor']['current']['dob']))
                        ]);
                    }
                }
            } catch (ApiException $e) {
                $this->getLogger()->alert(
                    'Unable to send SMS via Notify',
                    [
                        'exception' => $e->getMessage(),
                        'notify-message' => (string)$e->getResponse()->getBody(),
                        'number-original' => $contact['phone'],
                        'number-sanitised' => (new PhoneNumber($contact['phone']))->get(),
                    ]
                );
            }
        } // if receive-notifications

        //---

        return $reference;
    }

    private function getDonorNameForSms(string $donorName)
    {
        return substr($donorName, 0, self::MAX_SMS_DONOR_NAME_LENGTH - 1);
    }
}
