<?php

namespace Application\Storage\Json;

use Storage\Json\JsonObject;

class Message extends JsonObject implements MessageInterface
{
    protected $content = [];

    public function build()
    {

    }
}
