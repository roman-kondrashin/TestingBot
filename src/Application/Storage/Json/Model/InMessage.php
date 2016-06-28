<?php

namespace Application\Storage\Json\Model;

use Application\Storage\Json\Message;

class InMessage extends Message
{
    protected $content;

    public function __construct($content)
    {
        $this->content = $this->decode($content);
    }

    public function getUpdateId()
    {
        return $this->content->update_id;
    }

    public function getChatId()
    {
        return $this->content->message->chat->id;
    }

    public function getDate()
    {
        return $this->content->message->date;
    }

    public function getText()
    {
        return $this->content->message->text;
    }
}
