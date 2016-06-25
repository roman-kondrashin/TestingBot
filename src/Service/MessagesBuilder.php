<?php

namespace Service;

use Application\Storage\Json\Model\OutMessage;

class MessagesBuilder
{
    /** @var MessagesBuilder */
    private static $obj;

    /** @var OutMessage */
    private $message;

    private function __construct() {}
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}

    public static function getInstance()
    {
        if (null === self::$obj) {
            self::$obj = new self();
            self::$obj->message = new OutMessage();
        }

        return self::$obj;
    }
}
