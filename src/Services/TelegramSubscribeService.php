<?php


namespace App\Services;


use App\Entity\Route;
use App\Entity\FlightAvgPriceSubscribe;
use TelegramBot\Api\Client;
use Twig\Environment;

class TelegramSubscribeService
{
    private $telegramClient;

    private $twig;

    public function __construct(
        Client $telegramClient,
        Environment $twig
    ) {
        $this->telegramClient = $telegramClient;
        $this->twig = $twig;
    }

    public function notify(array $subscribers, Route $route, float $monthAvgPrice)
    {
        foreach($subscribers as $subscriber)
        {
            $this->notifySubscriber($subscriber, $route, $monthAvgPrice);
        }
    }

    private function notifySubscriber(FlightAvgPriceSubscribe $subscriber, Route $route, float $monthAvgPrice)
    {
        $message = $this->buildMessage($subscriber, $route, $monthAvgPrice);
        $this->telegramClient->sendMessage($subscriber->getChat(), $message);
    }

    private function buildMessage(FlightAvgPriceSubscribe $subscriber, Route $route, float $monthAvgPrice)
    {
        $data = ['subscriber' => $subscriber, 'route' => $route, 'monthAvgPrice' => $monthAvgPrice];

        return $this->twig->render('notification/telegram/notify-about-flight-price-drop.twig', $data);
    }
}