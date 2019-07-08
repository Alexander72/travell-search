<?php


namespace App\Controller;

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
    public function hook(Client $bot)
    {
        $bot->command('echo', function(Message $message) use ($bot) {
            $bot->sendMessage($message->getChat()->getId(), $message->getText());
        });

        $bot->run();

        return new Response();
    }
}