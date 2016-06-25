<?php

namespace Application\Storage\Json;

use Storage\Json\JsonObject;

class Message extends JsonObject implements MessageInterface
{
    private $content = [];

    public function build()
    {
    }
}
