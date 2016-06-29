<?php

namespace Application;

use Application\Storage\Json\Model\InMessage;
use Application\Storage\Json\Model\OutMessage;
use Storage\Db\Mysql;

class TestingBot
{
    const TOKEN = '221381282:AAHBNtrIFlNGgCB62Fu2Iq0gpUc-nR7_M9A';
    const API_URL = 'https://api.telegram.org/bot' . self::TOKEN . '/';

    const TEST_TYPE_SIMPLE = 0;
    const TEST_TYPE_COMPLEX = 1;

    const TEST_TYPE_SIMPLE_TEXT = 'Простой';
    const TEST_TYPE_COMPLEX_TEXT = 'Продвинутый';

    const MESSAGE_HELLO_TEXT = 'Добрый день!
Вас приветствует тест основы программирования!
Видео: https://www.youtube.com/watch?v=SW_UCzFO7X0
';
    const MESSAGE_HELLO_QUESTION = 'Выберите уровень сложности:';
    const MESSAGE_HELLO_KEYBOARD = [[self::TEST_TYPE_SIMPLE_TEXT, self::TEST_TYPE_COMPLEX_TEXT]];

    /** @var Config */
    private $config;
    /** @var Mysql */
    private $dbh;
    /** @var int */
    private $chatId;

    private $questions = [
        self::TEST_TYPE_SIMPLE => [
            1 => [
                'text' => 'Какое число зашифровано в двоичном коде "111"?',
                'answers' => ['111', '1', '7', '3'],
                'right_answer' => '7',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=500',
            ],
            2 => [
                'text' => 'Как в двоичном коде выглядит число 50?',
                'answers' => ['10010001', '00020202', '50', '00110010'],
                'right_answer' => '00110010',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=746',
            ],
            3 => [
                'text' => 'Что означают цифры "72" "73" "33" в ASCII?',
                'answers' => ['HI!', 'ASD', 'QWE', 'YES'],
                'right_answer' => 'HI!',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=849',
            ],
            4 => [
                'text' => 'Какая последовательность чисел используется для RGB?',
                'answers' => ['33 16 14', '72 73 33', '22 43 32', '33 22 11'],
                'right_answer' => '72 73 33',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=946',
            ],
        ],
        self::TEST_TYPE_COMPLEX => [
            1 => [
                'text' => 'Какое число будет в двоичном коде "01100111"?',
                'answers' => ['5', '122', '103', '111'],
                'right_answer' => '103',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=500',
            ],
            2 => [
                'text' => 'Как в двоичном коде выглядит число 300?',
                'answers' => ['100100010', '01020202', '100101100', '100110010'],
                'right_answer' => '100101100',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=746',
            ],
            3 => [
                'text' => 'Как будет обозначено HELP в ASCII?',
                'answers' => ['80 73 89 65', '001101110', '72 69 76 80', '02132123'],
                'right_answer' => '72 69 76 80',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=810',
            ],
            4 => [
                'text' => 'Сколько шагов нужно, чтобы найти запись в 4 миллиардах записей?',
                'answers' => ['64', '32', '16', '128'],
                'right_answer' => '32',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=1373',
            ],
        ],
    ];

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->dbh = new Mysql($this->config);
        $this->dbh->connect();
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

        $this->saveMessage($message);
        $number = $this->getCurrentQuestionNumber();

        // hello
        if (strpos($text, '/start') === 0) {
            $this->sendHelloMessage();
            $this->sendHelloQuestion();
        }
        // test level
        elseif ($number == 0 && $text != self::TEST_TYPE_SIMPLE_TEXT && $text != self::TEST_TYPE_COMPLEX_TEXT) {
            $this->sendHelloQuestion();
        } elseif ($number == 0) {
            $this->saveAnswer($message);

            $test_type = $this->getTestType($message);
            if ($text == self::TEST_TYPE_SIMPLE_TEXT || $text == self::TEST_TYPE_COMPLEX_TEXT) {
                $outMessage = new OutMessage();
                $outMessage->setChatId($this->chatId);
                $outMessage->setText($this->getQuestion($test_type, $number + 1));
                $outMessage->setKeyboard($this->getAnswerKeyboard($test_type, $number + 1));
            } else {
                $outMessage = $this->getHelloQuestion();
            }

            $this->sendMessage($outMessage);
        // in test
        } elseif ($number > 0) {
            $test_type = $this->getTestType($message);
            if ($number < count($this->questions[$test_type])) {
                if ($this->checkAnswersArray($test_type, $number, $text)) {
                    // get right answer
                    $try_number = $this->getTryNumber($test_type, $number);
                    $correct = $this->getRightAnswer($test_type, $number);
                    if ($correct == $text) {
                        // final
                        $this->saveAnswer($message, $number, $test_type, $try_number + 1, 1);
                        $outMessage = new OutMessage();
                        $outMessage->setChatId($this->chatId);
                        $outMessage->setText($this->getQuestion($test_type, $number + 1));
                        $outMessage->setKeyboard($this->getAnswerKeyboard($test_type, $number + 1));
                    } else {
                        $this->saveAnswer($message, $number, $test_type, $try_number + 1, 0);
                        $outMessage = new OutMessage();
                        $outMessage->setChatId($this->chatId);
                        $outMessage->setText($this->getWrongAnswer($test_type, $number));
                        $outMessage->setKeyboard($this->getAnswerKeyboard($test_type, $number));
                    }

                    $this->sendMessage($outMessage);
                } else {
                    $this->sendChooseAnswerMessage();
                }
            // out of test
            } else {

            }
        }
    }

    private function getTryNumber($test_type, $number)
    {
        $data = $this->dbh->fetchOne('SELECT `try_number` FROM `answer` WHERE `chat_id` = ' . $this->chatId . ' AND `question_id` = ' . $number . ' AND `test_type` = ' . $test_type . ' ORDER BY `update_id` DESC LIMIT 1;');
        return $data instanceof \stdClass ? $data->try_number - 1 : (int)$data;
    }

    private function checkAnswersArray($test_type, $number, $answer)
    {
        return in_array($answer, $this->getAnswers($test_type, $number));
    }

    private function getAnswerKeyboard($test_type, $number)
    {
        $result = [];

        $i = 0;
        $answers = $this->getAnswers($test_type, $number);
        foreach ($answers as $index => $answer) {
            if ($index != 0 && $index % 2 == 0) {
                $i++;
            }

            $answer[$i][] = $answer;
        }

        return $result;
    }

    private function getWrongAnswer($test_type, $number)
    {
        return $this->questions[$test_type][$number]['wrong_answer'];
    }

    private function getRightAnswer($test_type, $number)
    {
        return $this->questions[$test_type][$number]['right_answer'];
    }

    private function getAnswers($test_type, $number)
    {
        return $this->questions[$test_type][$number]['answers'];
    }

    private function getQuestion($test_type, $number)
    {
        return $this->questions[$test_type][$number]['text'];
    }

    private function getTestType(InMessage $message)
    {
        $text = $message->getText();

        if ($text == self::TEST_TYPE_SIMPLE_TEXT) {
            $test_type = 0;
        } elseif ($text == self::TEST_TYPE_COMPLEX_TEXT) {
            $test_type = 1;
        } else {
            $data = $this->dbh->fetchOne('SELECT `test_type` FROM `answer` WHERE `chat_id` = ' . $message->getChatId() . ' ORDER BY `update_id` DESC LIMIT 1;');
            $test_type = $data instanceof \stdClass ? $data->test_type : (int)$data;
        }

        return $test_type;
    }

    private function saveAnswer(InMessage $message, $questionId = 0, $test_type = null, $try_number = 0, $is_final = 1)
    {
        $test_type = $this->getTestType($message);
        $sql = <<< SQL
            INSERT INTO `answer` (`update_id`, `chat_id`, `question_id`, `test_type`, `try_number`, `answer`, `is_final`)
            VALUES(
                {$message->getUpdateId()},
                {$message->getChatId()},
                {$questionId},
                {$test_type},
                {$try_number},
                '{$message->getText()}',
                {$is_final}
            );
SQL;

        $this->dbh->query($sql);
    }

    private function saveMessage(InMessage $message)
    {
        // save message
        $sql = <<< SQL
            INSERT INTO `message` (`update_id`, `chat_id`, `date`, `text`) 
            VALUES(
                {$message->getUpdateId()},
                {$message->getChatId()},
                {$message->getDate()},
                '{$message->getText()}'
            );
SQL;
        $this->dbh->query($sql);
    }

    private function getCurrentQuestionNumber()
    {
        $data = $this->dbh->fetchOne('SELECT COUNT(*) AS `cnt` FROM `answer` WHERE `chat_id` = ' . $this->chatId . ' AND `is_final` = 1');

        return $data instanceof \stdClass ? $data->cnt : (int)$data;
    }

    private function sendChooseAnswerMessage()
    {
        $message = new OutMessage();
        $message->setChatId($this->chatId);
        $message->setText('Выберите один из ответов!');

        $this->sendMessage($message);
    }

    private function sendHelloMessage()
    {
        $this->sendMessage($this->getHelloMessage());
    }

    private function sendHelloQuestion()
    {
        $this->sendMessage($this->getHelloQuestion());
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
        $message->setText(self::MESSAGE_HELLO_TEXT);

        return $message;
    }

    private function getHelloQuestion()
    {
        $message = new OutMessage();
        $message->setChatId($this->chatId);
        $message->setText(self::MESSAGE_HELLO_QUESTION);
        $message->setKeyboard(self::MESSAGE_HELLO_KEYBOARD);

        return $message;
    }

    public function __destruct()
    {
        $this->dbh->close();
    }
}

