<?php


namespace App\Telegram\Events;


use Symfony\Component\EventDispatcher\Event;
use TelegramBot\Api\Types\Message;

class MessageSentEvent extends Event
{
    const NAME = 'telegram.message.sent';

    /**
     * @var Message
     */
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}