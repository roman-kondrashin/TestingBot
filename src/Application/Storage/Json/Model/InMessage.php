<?php

namespace Application\Storage\Json\Model;

use Application\Storage\Json\Message;

class InMessage extends Message
{
    private $content;

    public function __construct($content)
    {
//        $this->content = $this->decode($content);
        $this->content = $content;
    }

    public function getStatus()
    {
        return $this->content->ok;
    }

    public function getResult()
    {
        return $this->content->result;
    }

    public function getContent()
    {
        return $this->getResult()->message;
    }
}
