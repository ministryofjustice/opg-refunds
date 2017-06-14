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

        $reference = $this->dataHandler->store( $data );

        $contact = $data['contact'];


        if (isset($contact['email']) && !empty($contact['email'])) {
            // Send email...

            $response = $this->notifyClient->sendEmail( $contact['email'], '4664b7ca-18b0-46e0-9a2f-e01becf45cdd', [
                'ref' => IdentFormatter::format($reference),
            ]);

        }


        if (false && isset($contact['mobile']) && !empty($contact['mobile'])) {
            // Send email...

            $response = $this->notifyClient->sendSms( $contact['mobile'], '8292b07c-4dcf-4240-8636-e5ed2f7c4d36', [
                'ref' => IdentFormatter::format($reference),
            ]);

        }


        return $reference;
    }

}
