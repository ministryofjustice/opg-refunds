<?php

$files = array(
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
);

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

use Ingestion\Worker\IngestionWorker;
use Zend\Log\Logger;

// Setup Logging

/** @var Logger $logger */
$logger = $container->get(Logger::class);

$logger->notice('Initialising ingestion daemon');

// Create the Worker

/** @var IngestionWorker $worker */
$worker = $container->get(IngestionWorker::class);

// Hook into system events
declare(ticks = 1);
pcntl_signal(SIGTERM, array($worker, 'stop'));
pcntl_signal(SIGINT, array($worker, 'stop'));
pcntl_signal(SIGQUIT, array($worker, 'stop'));

// Start worker. Will block until stopped
try {
    $successful = $worker->run();
} catch (Exception $ex) {
    $logger->crit("An unknown exception has caused the worker to terminate", [ 'exception'=>$ex ]);
    exit(1);
}

$logger->notice('Ingestion daemon ended ' . ($successful ? 'successfully' : 'unsuccessfully'));

// Exit with correct code based on worker response
if ($successful) {
    exit(0);
} else {
    exit(1);
}
