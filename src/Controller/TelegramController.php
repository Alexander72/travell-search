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
    private $telegramHookToken;

    /**
     * TelegramController constructor.
     * @param string $telegramHookToken
     */
    public function __construct(string $telegramHookToken)
    {
        $this->telegramHookToken = $telegramHookToken;
    }

    /**
     * @Route("/api/v1/telegram/hook/{token}", name="api_telegram_hook")
     */
    public function hook(string $token, Client $bot, EntityManagerInterface $em)
    {
        if($token !== $this->telegramHookToken)
        {
            return new Response('', 403);
        }

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

        $bot->command('get_subscribes', function(Message $message) use ($bot, $em) {
            $telegramMessage = new TelegramMessage();
            $telegramMessage->setDirection(TelegramMessage::DIRECTION_INCOME);
            $telegramMessage->setChatId($message->getChat()->getId());
            $telegramMessage->setText($this>$this->render(''));
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