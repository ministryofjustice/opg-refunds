<?php

$files = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];

$found = false;
foreach ($files as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}

if (!class_exists('Composer\Autoload\ClassLoader', false)) {
    die(
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

cli\Colors::enable();

// Initialise IoC container
$container = require __DIR__ . '/../../config/container.php';

use App\Service\Notify;
use Zend\Log\Logger;

// Setup Logging

/** @var Logger $logger */
$logger = $container->get(Logger::class);

// Check service is enabled

$config = $container->get('config');

if (!isset($config['notify']['enabled'])) {
    $logger->emerg("Notify service missing 'enabled' configuration");
    exit(1);
}

if (!isset($config['notify']['user_id'])) {
    $logger->emerg("Notify service missing 'user_id' configuration");
    exit(1);
}

$userId = (int)$config['notify']['user_id'];

if (!isset($config['notify']['max_notifications'])) {
    $logger->emerg("Notify service missing 'max_notifications' configuration");
    exit(1);
}

$maxNotifications = (int)$config['notify']['max_notifications'];

if ($config['notify']['enabled'] === false) {
    cli\line('Notify service is not enabled; stopping');
    $logger->alert("Warning - notify service not enabled");
    exit(0);
}

// Get the notify service

/** @var Notify $notifyService */
$notifyService = $container->get(Notify::class);

cli\line('Notify service initialisation complete. Starting with user id ' . $userId . ' and max notifications ' . $maxNotifications);

$notified = $notifyService->notifyAll($userId, null, $maxNotifications);

cli\line('Notify service executed successfully. Processed ' . $notified['processed'] . ' from a total of ' . $notified['total'] . '. ' . ($notified['total'] - $notified['processed']) . ' remaining that can be sent automatically. There are ' . count($notified['letters']) . ' letters and ' . count($notified['phoneCalls']) . ' phone calls to be processed manually.');

exit(0);
