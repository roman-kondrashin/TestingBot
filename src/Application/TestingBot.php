<?php

namespace Application;

use Application\Storage\Json\Model\InMessage;

class TestingBot
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getMessage()
    {
        $content = file_get_contents('php://input');
        return new InMessage($content);
    }

    public function handle()
    {
        $message = $this->getMessage();
        $content = $message->getContent();
        $f = fopen('tmp.txt', 'w');
        fwrite($f, $content);
        fclose($f);
        die();
        if ($message->getStatus()) {
            $content = $message->getContent();
            var_dump($content);
        }
    }
}
