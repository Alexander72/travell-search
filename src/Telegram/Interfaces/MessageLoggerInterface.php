<?php


namespace App\Telegram\Interfaces;

use TelegramBot\Api\Types\Message;

interface MessageLoggerInterface
{
    public function log(Message $message, string $direction);
}