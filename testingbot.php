<?php

use Application\TestingBot;
use Service\ServiceLocator;

require_once 'bootstrap.php';

$si = ServiceLocator::getInstance();

$app = new TestingBot(
    $si->get('config')
);

var_dump($app->getConfig());
