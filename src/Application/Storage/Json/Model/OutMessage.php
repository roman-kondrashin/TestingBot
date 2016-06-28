<?php

namespace Application\Storage\Json\Model;

use Application\Storage\Json\Message;

class OutMessage extends Message
{
    /** @var string */
    private $method;
    /** @var int */
    private $chatId;
    /** @var string */
    private $text;
    /** @var array */
    private $keyboard = [];

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param int $chatId
     */
    public function setChatId($chatId)
    {
        $this->chatId = $chatId;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    public function setKeyboard(array $keyboard)
    {
        $this->keyboard = $keyboard;
    }

    public function build()
    {
        $this->content = [
            'method' => $this->method,
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'reply_markup' => [
                'keyboard' => $this->keyboard,
                'resize_keyboard' => true,
            ],
        ];

        return $this->encode($this->content);
    }
}
