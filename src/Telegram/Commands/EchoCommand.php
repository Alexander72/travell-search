<?php


namespace App\Telegram\Commands;

use App\Telegram\Events\MessageSentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class EchoCommand
{
    const NAME = 'echo';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Client $client
    ) {
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(Message $message): void
    {
        $response = $this->client->sendMessage($message->getChat()->getId(), $message->getText());

        $this->eventDispatcher->dispatch(MessageSentEvent::NAME, new MessageSentEvent($response));
    }
}