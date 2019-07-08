<?php


namespace App\Controller;

use App\Entity\TelegramMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class TelegramController extends AbstractController
{
    /**
     * @Route("/api/v1/telegram/hook", name="api_telegram_hook")
     */
    public function hook(Client $bot, EntityManagerInterface $em)
    {
        file_put_contents('/motivity/var/log/tg.log', file_get_contents('php://input'), FILE_APPEND);

        $bot->command('echo', function(Message $message) use ($bot, $em) {
            $telegramMessage = new TelegramMessage();
            $telegramMessage->setDirection(TelegramMessage::DIRECTION_INCOME);
            $telegramMessage->setChatId($message->getChat());
            $telegramMessage->setText($message->getText());
            $telegramMessage->setDate(new \DateTime());
            $telegramMessage->setMessageId($message->getMessageId());
            $em->flush($telegramMessage);

            $bot->sendMessage($telegramMessage->getChatId(), $telegramMessage->getText());
        });

        $bot->run();

        return new Response();
    }
}