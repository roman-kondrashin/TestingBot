<?php

namespace Application;

use Application\Storage\Json\Model\InMessage;
use Application\Storage\Json\Model\OutMessage;
use Storage\Db\Mysql;

class TestingBot
{
    const TOKEN = '221381282:AAHBNtrIFlNGgCB62Fu2Iq0gpUc-nR7_M9A';
    const API_URL = 'https://api.telegram.org/bot' . self::TOKEN . '/';

    const MESSAGE_HELLO = "Добрый день!
Вас приветствует тест основы программирования!
Видео: https://www.youtube.com/watch?v=SW_UCzFO7X0
Выберите уровень сложности:\n
";
    const MESSAGE_HELLO_KEYBOARD = [["Простой", "Продвинутый"]];

    /** @var Config */
    private $config;
    /** @var Mysql */
    private $dbh;
    /** @var int */
    private $chatId;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->dbh = new Mysql($this->config);
    }

    /**
     * @return InMessage
     */
    public function getMessage()
    {
        $content = file_get_contents('php://input');
        return new InMessage($content);
    }

    public function handle()
    {
        $message = $this->getMessage();

        $this->chatId = $message->getChatId();
        $text = $message->getText();

        // save message
        $sql = <<< SQL
            INSERT INTO `message` (`update_id`, `chat_id`, `date`, `text`) 
            VALUES(
              {$message->getUpdateId()},
              {$this->chatId},
              {$message->getDate()},
              '{$text}'
            );
SQL;
        $this->dbh->connect();
        $this->dbh->query($sql);
        $this->dbh->close();

        if (strpos($text, '/start') === 0) {
            $this->sendHelloMessage();
        }
    }

    private function sendHelloMessage()
    {
        $this->sendMessage($this->getHelloMessage());
    }

    private function sendMessage(OutMessage $message)
    {
        $message->setMethod('sendMessage');

        $handle = curl_init(self::API_URL);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $message->build());
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        return curl_exec($handle);
    }

    private function getHelloMessage()
    {
        $message = new OutMessage();
        $message->setChatId($this->chatId);
        $message->setText(self::MESSAGE_HELLO);
        $message->setKeyboard(self::MESSAGE_HELLO_KEYBOARD);

        return $message;
    }
}

