<?php

namespace App\Command;

use App\Entity\TelegramMessage;
use App\Telegram\Commands\EchoCommand;
use App\Telegram\Events\MessageReceivedEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class TelegramGetUpdatesCommand extends Command
{
    protected static $defaultName = 'telegram:get-updates';
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
        parent::__construct();
        $this->client = $client;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new EchoCommand($this->eventDispatcher, $this->client);
        $this->client->command(EchoCommand::NAME, function(Message $message) use($command) {

            $this->eventDispatcher->dispatch(MessageReceivedEvent::NAME, new MessageReceivedEvent($message));

            $command->run($message);
        });

        //$this->client->setCurlOption('CURLOPT_SSL_VERIFYPEER', false);
        $this->client->handle($this->client->getUpdates());
    }
}
