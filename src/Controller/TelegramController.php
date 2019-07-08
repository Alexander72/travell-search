<?php


namespace App\Controller;

use App\Entity\TelegramMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

class TelegramController extends AbstractController
{
    /**
     * @Route("/api/v1/telegram/hook", name="api_telegram_hook")
     */
    public function hook(Client $bot, EntityManagerInterface $em)
    {
        //$result = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $s), true);

        //return new Response(var_export($result, 1));
        file_put_contents('/motivity/var/log/tg.log', "\n".file_get_contents('php://input'), FILE_APPEND);

        $bot->command('echo', function(Message $message) use ($bot, $em) {
            $telegramMessage = new TelegramMessage();
            $telegramMessage->setDirection(TelegramMessage::DIRECTION_INCOME);
            $telegramMessage->setChatId($message->getChat()->getId());
            $telegramMessage->setText($message->getText());
            $telegramMessage->setDate(new \DateTime());
            $telegramMessage->setMessageId($message->getMessageId());
            $em->persist($telegramMessage);
            $em->flush();

            $bot->sendMessage($telegramMessage->getChatId(), $telegramMessage->getText());
        });

        $rawBody = $bot->getRawBody();
        if($data = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rawBody), true))
        {
            $bot->handle([Update::fromResponse($data)]);
        }

        return new Response();
    }
}