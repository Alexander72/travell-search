<?php


namespace App\Telegram\Loggers;


use App\Entity\TelegramMessage;
use App\Telegram\Interfaces\MessageLoggerInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use TelegramBot\Api\Types\Message;

class MessageDatabaseLogger implements MessageLoggerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function log(Message $message, string $direction)
    {
        $telegramMessage = new TelegramMessage();
        $telegramMessage->setDirection($direction);
        $telegramMessage->setChatId($message->getChat()->getId());
        $telegramMessage->setText($message->getText());
        $telegramMessage->setDate(DateTime::createFromFormat('U', $message->getDate()));
        $telegramMessage->setMessageId($message->getMessageId());

        $this->entityManager->persist($telegramMessage);
        $this->entityManager->flush();
    }
}