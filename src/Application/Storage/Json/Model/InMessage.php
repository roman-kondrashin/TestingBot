<?php

namespace Application\Storage\Json\Model;

use Application\Storage\Json\Message;

class InMessage extends Message
{
    private $content;
    
    public function __construct($content)
    {
        $this->content = $this->decode($content);
    }
}
