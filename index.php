<?php

use Application\Config;
use Application\TestingBot;
use Storage\File\Model\Ini;

require_once 'bootstrap.php';

$app = new TestingBot(
    new Config(
        new Ini('config/config.ini')
    )
);

$app->handle();
