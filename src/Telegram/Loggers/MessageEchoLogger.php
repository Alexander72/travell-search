<?php


namespace App\Telegram\Loggers;


use App\Telegram\Interfaces\MessageLoggerInterface;
use TelegramBot\Api\Types\Message;

class MessageEchoLogger implements MessageLoggerInterface
{
    public function log(Message $message, string $direction)
    {
        echo "DIRECTION: $direction\n";
        var_dump($message);
    }
}