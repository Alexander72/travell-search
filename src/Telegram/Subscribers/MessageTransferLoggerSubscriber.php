<?php


namespace App\Telegram\Subscribers;


use App\Entity\TelegramMessage;
use App\Telegram\Events\MessageReceivedEvent;
use App\Telegram\Events\MessageSentEvent;
use App\Telegram\Interfaces\MessageLoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageTransferLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var MessageLoggerInterface
     */
    private $logger;

    public function __construct(MessageLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            /** @see onMessageSentEvent */
            MessageSentEvent::NAME => 'onMessageSentEvent',
            /** @see onMessageReceiveEvent */
            MessageReceivedEvent::NAME => 'onMessageReceiveEvent',
        ];
    }

    public function onMessageSentEvent(MessageSentEvent $event)
    {
        $this->logger->log($event->getMessage(), TelegramMessage::DIRECTION_INCOME);
    }

    public function onMessageReceiveEvent(MessageReceivedEvent $event)
    {
        $this->logger->log($event->getMessage(), TelegramMessage::DIRECTION_OUTCOME);
    }
}