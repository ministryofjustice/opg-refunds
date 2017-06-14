<?php

namespace App\Service\Notify;

use Interop\Container\ContainerInterface;

use Alphagov\Notifications\Client as NotifyClient;

/**
 * Factory to create an instance of the GOV UK Notify client,
 * pre-configured with a HTTP client and API key.
 *
 * Class NotifyClientFactory
 * @package App\Service\Notify
 */
class NotifyClientFactory
{

    /**
     * @param ContainerInterface $container
     * @return NotifyClient
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['notify']['api']['key'])) {
            throw new \UnexpectedValueException('Notify API key not configured');
        }

        //---

        return new NotifyClient([
            'apiKey' => $config['notify']['api']['key'],
            'httpClient' => $container->get(\Http\Client\HttpClient::class)
        ]);
    }
}
