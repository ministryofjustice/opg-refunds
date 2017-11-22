<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;

class Notify
{
    /**
     * @var NotifyClient
     */
    private $notifyClient;

    public function __construct(NotifyClient $notifyClient)
    {
        $this->notifyClient = $notifyClient;
    }
}