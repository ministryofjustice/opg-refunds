<?php
namespace App\Service\Refund;

use Alphagov\Notifications\Client as NotifyClient;

class ProcessApplication
{

    private $notifyClient;

    public function __construct( NotifyClient $notifyClient )
    {
        $this->notifyClient = $notifyClient;
    }

    public function process( $data )
    {

        $ref = time();

        $contact = $data['contact'];

        if( isset($contact['email']) ){

            // Send email...

            $response = $this->notifyClient->sendEmail( $contact['email'], '4664b7ca-18b0-46e0-9a2f-e01becf45cdd', [
                'ref' => $ref,
            ]);

        }


        if( isset($contact['mobile'])){

            // Send email...

            $response = $this->notifyClient->sendSms( $contact['mobile'], '8292b07c-4dcf-4240-8636-e5ed2f7c4d36', [
                'ref' => $ref,
            ]);

        }

    }

}
