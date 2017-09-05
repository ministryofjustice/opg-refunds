<?php
$container = require 'config/container.php';

/* @var $em \Doctrine\ORM\EntityManager */
$em = $container->get('doctrine.entity_manager.orm_auth_migration');

return new \Symfony\Component\Console\Helper\HelperSet([
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em),
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection())
]);