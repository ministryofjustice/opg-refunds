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

cli\line('Waiting until all services are up and migration is complete before Initialising ingestion daemon');
sleep(60);
cli\line('Initialising ingestion daemon');

// Initialise IoC container
$container = require __DIR__ . '/../../config/container.php';

use Ingestion\Worker\IngestionWorker;
use Zend\Log\Logger;

// Setup Logging

/** @var Logger $logger */
$logger = $container->get(Logger::class);

// Check daemon is enabled

$config = $container->get('config');

if (!isset($config['ingestion']['enabled'])) {
    $logger->emerg("Ingestion daemon missing 'enabled' configuration");
    exit(1);
}

if ($config['ingestion']['enabled'] === false) {
    cli\line('Ingestion daemon is not enabled; stopping');
    $logger->alert("Warning - ingestion daemon not enabled");
    exit(0);
}

// Create the Worker

/** @var IngestionWorker $worker */
$worker = $container->get(IngestionWorker::class);

// Hook into system events
declare(ticks = 1);
pcntl_signal(SIGTERM, [$worker, 'stop']);
pcntl_signal(SIGINT, [$worker, 'stop']);
pcntl_signal(SIGQUIT, [$worker, 'stop']);

cli\line('Ingestion daemon initialisation complete. Starting');

// Start worker. Will block until stopped
try {
    $successful = $worker->run();
} catch (Exception $ex) {
    $logger->crit("An unknown exception has caused the worker to terminate", [ 'exception'=>$ex ]);
    exit(1);
}

cli\line('Ingestion daemon ended ' . ($successful ? 'successfully' : 'unsuccessfully'));

// Exit with correct code based on worker response
if ($successful) {
    exit(0);
} else {
    exit(1);
}
