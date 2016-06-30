<?php

namespace Application;

use Application\Storage\Json\Model\InMessage;
use Application\Storage\Json\Model\OutMessage;
use Storage\Db\Mysql;

class TestingBot
{
//    const TOKEN = '221381282:AAHBNtrIFlNGgCB62Fu2Iq0gpUc-nR7_M9A';
    const TOKEN = '227815068:AAHPqrZo7YXh93NxXwOSy80UP3acSPTVPs0';
    const API_URL = 'https://api.telegram.org/bot' . self::TOKEN . '/';

    const TEST_TYPE_SIMPLE = 0;
    const TEST_TYPE_COMPLEX = 1;

    const MESSAGE_GET_VIDEO = 'Смотреть видео';

    const TEST_TYPE_SIMPLE_TEXT = 'Простой';
    const TEST_TYPE_COMPLEX_TEXT = 'Продвинутый';

    const MESSAGE_START_AGAIN = 'Пройти тест еще раз';
    const MESSAGE_CONTINUE = 'Продолжить';
    const MESSAGE_SEND_RESULTS = 'Закончить и отправить результат';

    const MESSAGE_HELLO1_TEXT = 'Добрый день!
Вас приветствует тренинг и тест по основам программирования!
Для прохождения теста ознакомтесь с видео.
';
    const MESSAGE_HELLO1_KEYBOARD = [[self::MESSAGE_GET_VIDEO]];

    const MESSAGE_HELLO2_TEXT = 'https://www.youtube.com/watch?v=SW_UCzFO7X0';
    const MESSAGE_HELLO2_QUESTION = 'Выберите уровень сложности:';
    const MESSAGE_HELLO2_KEYBOARD = [[self::TEST_TYPE_SIMPLE_TEXT, self::TEST_TYPE_COMPLEX_TEXT]];

    const MESSAGE_WRONG_TEXT = 'Не верно, посмотрите этот момент в лекции ещё раз пожалуйста.';

    const MESSAGE_RESULT_TEXT_SIMPLE = 'Спасибо!
Вы прошли тест на %2.2f из %d возможных баллов.
Тест считается успешно пройденным, если вы набрали более 3х баллов из 4 возможных, с 1-го раза.
';
    const MESSAGE_RESULT_TEXT_COMPLEX = 'Спасибо!
Вы прошли тест на %2.2f из %d возможных баллов.
Тест считается успешно пройденным, если вы набрали более 6ти баллов из 8 возможных, с 1-го раза.
';
    const MESSAGE_RESULT_KEYBOARD = [[self::MESSAGE_START_AGAIN, self::MESSAGE_CONTINUE, self::MESSAGE_SEND_RESULTS]];

    /** @var Config */
    private $config;
    /** @var Mysql */
    private $dbh;
    /** @var int */
    private $chatId;

    private $questions = [
        self::TEST_TYPE_SIMPLE => [
            1 => [
                'text' => 'Вопрос 1 из 5:  https://fs00.infourok.ru/images/doc/246/250438/img1.jpg',
                'answers' => ['Квадрат', 'Круг', 'Треугольник', 'Прямоугольник', 'Зигзаг'],
                'right_answer' => true,
                'results' => [
                    1 => 0,
                ],
            ],
            2 => [
                'text' => 'Вопрос 2 из 5: Какое число зашифровано в двоичном коде "111"?',
                'answers' => ['111', '1', '7', '3'],
                'right_answer' => '7',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=500',
                'results' => [
                    1 => 1,
                    2 => 0.5,
                    3 => 0.35,
                    4 => 0.25,
                ],
            ],
            3 => [
                'text' => 'Вопрос 3 из 5: Как в двоичном коде выглядит число 50?',
                'answers' => ['10010001', '00020202', '50', '00110010'],
                'right_answer' => '00110010',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=746',
                'results' => [
                    1 => 1,
                    2 => 0.5,
                    3 => 0.35,
                    4 => 0.25,
                ],
            ],
            4 => [
                'text' => 'Вопрос 4 из 5: Что означают цифры "72" "73" "33" в ASCII?',
                'answers' => ['HI!', 'ASD', 'QWE', 'YES'],
                'right_answer' => 'HI!',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=849',
                'results' => [
                    1 => 1,
                    2 => 0.5,
                    3 => 0.35,
                    4 => 0.25,
                ],
            ],
            5 => [
                'text' => 'Вопрос 5 из 5: Какая последовательность чисел используется для RGB?',
                'answers' => ['33 16 14', '72 73 33', '22 43 32', '33 22 11'],
                'right_answer' => '72 73 33',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=946',
                'results' => [
                    1 => 1,
                    2 => 0.5,
                    3 => 0.35,
                    4 => 0.25,
                ],
            ],
        ],
        self::TEST_TYPE_COMPLEX => [
            1 => [
                'text' => 'Вопрос 1 из 5: https://fs00.infourok.ru/images/doc/246/250438/img1.jpg',
                'answers' => ['Квадрат', 'Круг', 'Треугольник', 'Прямоугольник', 'Зигзаг'],
                'right_answer' => true,
                'results' => [
                    1 => 0,
                ],
            ],
            2 => [
                'text' => 'Вопрос 2 из 5: Какое число будет в двоичном коде "01100111"?',
                'answers' => ['5', '122', '103', '111'],
                'right_answer' => '103',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=500',
                'results' => [
                    1 => 1,
                    2 => 0.5,
                    3 => 0.35,
                    4 => 0.25,
                ],
            ],
            3 => [
                'text' => 'Вопрос 3 из 5: Как в двоичном коде выглядит число 300?',
                'answers' => ['100100010', '01020202', '100101100', '100110010'],
                'right_answer' => '100101100',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=746',
                'results' => [
                    1 => 3,
                    2 => 2,
                    3 => 1,
                    4 => 0.5,
                ],
            ],
            4 => [
                'text' => 'Вопрос 4 из 5: Как будет обозначено HELP в ASCII?',
                'answers' => ['80 73 89 65', '001101110', '72 69 76 80', '02132123'],
                'right_answer' => '72 69 76 80',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=810',
                'results' => [
                    1 => 1,
                    2 => 0.5,
                    3 => 0.35,
                    4 => 0.25,
                ],
            ],
            5 => [
                'text' => 'Вопрос 5 из 5: Сколько шагов нужно, чтобы найти запись в 4 миллиардах записей?',
                'answers' => ['64', '32', '16', '128'],
                'right_answer' => '32',
                'wrong_answer' => 'https://youtu.be/SW_UCzFO7X0?t=1373',
                'results' => [
                    1 => 3,
                    2 => 2,
                    3 => 1,
                    4 => 0.5,
                ],
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

        // again
        if (
            $text == self::MESSAGE_START_AGAIN
            || $text == self::MESSAGE_CONTINUE
            || $text == self::MESSAGE_SEND_RESULTS
        ) {
            $this->clearResults();
            $text = '/start';
        }

        // hello
        if (strpos($text, '/start') === 0) {
            $this->clearResults();
            $this->sendHello1Message();
        }
        elseif ($text == self::MESSAGE_GET_VIDEO) {
            $this->sendHelloMessage();
            $this->sendHelloQuestion();
        }
        // test level
        elseif ($number == 0 && $text != self::TEST_TYPE_SIMPLE_TEXT && $text != self::TEST_TYPE_COMPLEX_TEXT) {
            $this->sendHelloQuestion();
            return;
        } elseif ($number == 0) {
            $this->saveAnswer($message);

            $test_type = $this->getTestType($message);
            if ($text == self::TEST_TYPE_SIMPLE_TEXT || $text == self::TEST_TYPE_COMPLEX_TEXT) {
                $outMessage = new OutMessage();
                $outMessage->setChatId($this->chatId);
                $outMessage->setText($this->getQuestion($test_type, $number + 1));
                $outMessage->setKeyboard($this->getAnswerKeyboard($test_type, $number + 1));
                $this->sendMessage($outMessage);
            } else {
                $outMessage = $this->getHelloQuestion();
                $this->sendMessage($outMessage);
            }
        // in test
        } elseif ($number > 0) {
            $test_type = $this->getTestType($message);
            if ($number <= count($this->questions[$test_type])) {
                if ($this->checkAnswersArray($test_type, $number, $text)) {
                    // get right answer
                    $try_number = $this->getTryNumber($test_type, $number);
                    $correct = $this->getRightAnswer($test_type, $number);
                    if ($correct === true || $correct == $text) {
                        // final
                        $this->saveAnswer($message, $number, $test_type, $try_number + 1, 1);
                        $outMessage = new OutMessage();
                        // last question
                        if ($number == count($this->questions[$test_type])) {
                            $result = $this->calculateResult($test_type);
                            $outMessage->setChatId($this->chatId);
                            $text = $test_type ? self::MESSAGE_RESULT_TEXT_COMPLEX : self::MESSAGE_RESULT_TEXT_SIMPLE;
                            $outMessage->setText(sprintf($text, $result->user, $result->max));
                            $outMessage->setKeyboard(self::MESSAGE_RESULT_KEYBOARD);
                        } else {
                            $outMessage->setChatId($this->chatId);
                            $outMessage->setText($this->getQuestion($test_type, $number + 1));
                            $outMessage->setKeyboard($this->getAnswerKeyboard($test_type, $number + 1));
                        }
                    } else {
                        $this->saveAnswer($message, $number, $test_type, $try_number + 1, 0);
                        $outMessage = new OutMessage();
                        $outMessage->setChatId($this->chatId);
                        $outMessage->setText(self::MESSAGE_WRONG_TEXT . "\n" . $this->getWrongAnswer($test_type, $number));
                        $outMessage->setKeyboard($this->getAnswerKeyboard($test_type, $number));
                    }

                    $this->sendMessage($outMessage);
                } else {
                    $this->sendChooseAnswerMessage();
                }
            // out of test
            } else {
                $outMessage = new OutMessage();
                $outMessage->setChatId($this->chatId);
                $outMessage->setText('Done');
            }
        }
    }

    private function clearResults()
    {
        $this->dbh->query('DELETE FROM `message` WHERE `chat_id` = ' . $this->chatId);
        $this->dbh->query('DELETE FROM `answer` WHERE `chat_id` = ' . $this->chatId);
    }

    private function calculateResult($test_type)
    {
        $result = new \stdClass();
        $result->user = 0;
        $result->max = 0;

        $data = $this->dbh->fetch('SELECT `question_id`, MAX(`try_number`) AS `try_number` FROM `answer` WHERE `chat_id` = ' . $this->chatId . ' AND `test_type` = ' . $test_type . ' AND `question_id` != 0 GROUP BY `question_id`;');
        foreach ($data as $answer) {
            $results = $this->getResults($test_type, $answer->question_id);
            $try_number = $answer->try_number;
            $maxTry = max(array_keys($results));
            if ($try_number > $maxTry) {
                $result->user += $results[$maxTry];
            } else {
                $result->user += $results[$try_number];
            }
            $result->max += $results[1];
        }

        return $result;
    }

    private function getTryNumber($test_type, $number)
    {
        $data = $this->dbh->fetchOne('SELECT `try_number` FROM `answer` WHERE `chat_id` = ' . $this->chatId . ' AND `question_id` = ' . $number . ' AND `test_type` = ' . $test_type . ' ORDER BY `update_id` DESC LIMIT 1;');
        return $data instanceof \stdClass ? $data->try_number : (int)$data;
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

            $result[$i][] = $answer;
        }

        return $result;
    }

    private function getResults($test_type, $number)
    {
        return $this->questions[$test_type][$number]['results'];
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

    private function sendHello1Message()
    {
        $outMessage = new OutMessage();
        $outMessage->setChatId($this->chatId);
        $outMessage->setText(self::MESSAGE_HELLO1_TEXT);
        $outMessage->setKeyboard(self::MESSAGE_HELLO1_KEYBOARD);

        $this->sendMessage($outMessage);
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
        $message->setText(self::MESSAGE_HELLO2_TEXT);

        return $message;
    }

    private function getHelloQuestion()
    {
        $message = new OutMessage();
        $message->setChatId($this->chatId);
        $message->setText(self::MESSAGE_HELLO2_QUESTION);
        $message->setKeyboard(self::MESSAGE_HELLO2_KEYBOARD);

        return $message;
    }

    public function __destruct()
    {
        $this->dbh->close();
    }
}

