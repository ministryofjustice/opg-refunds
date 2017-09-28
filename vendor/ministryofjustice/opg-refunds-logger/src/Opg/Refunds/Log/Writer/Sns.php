<?php
namespace Opg\Refunds\Log\Writer;

use Throwable;

use Zend\Log\Writer;
use Zend\Log\Formatter\Simple as SimpleFormatter;

use Aws\Sns\SnsClient;

class Sns extends Writer\AbstractWriter implements Writer\WriterInterface
{

    private $snsClient = null;
    private $snsEndpoints = null;

    public function __construct(SnsClient $snsClient, array $snsEndpoints, $options = null)
    {
        parent::__construct($options);
        $this->snsClient = $snsClient;
        $this->snsEndpoints = $snsEndpoints;

        $this->setFormatter( new SimpleFormatter );
    }

    public function doWrite(array $event)
    {
        try {
            $message = $this->getFormatter()->format($event);

            $notification = [
                'MessageStructure' => 'string',
                'Message' => $message,
            ];

            // Loops over the SNS topics we have
            foreach ($this->snsEndpoints as $type=>$details) {

                // If a topic supports the current event priority...
                if (in_array($event['priority'], $details['priorities'])) {

                    try {
                        $this->snsClient->publish(
                            $notification + [
                                'TopicArn' => $details['arn'],
                                'Subject' => ucwords($type).' alert from OPG Refunds',
                            ]
                        );
                    } catch (Throwable $e)
                    {
                        throw $e;
                    }

                } // if

            } // foreach
        } catch (Throwable $e)
        {
            throw $e;
        }

    }

}
