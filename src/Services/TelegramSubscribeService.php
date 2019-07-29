<?php


namespace App\Services;


use App\Entity\Route;
use App\Entity\FlightAvgPriceSubscribe;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use Twig\Environment;

class TelegramSubscribeService
{
    private $telegramClient;

    private $twig;
    private $logger;

    public function __construct(
        Client $telegramClient,
        Environment $twig,
        LoggerInterface $logger
    ) {
        $this->telegramClient = $telegramClient;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function notify(array $subscribes, Route $route, ?float $monthAvgPrice)
    {
        foreach($subscribes as $subscribe)
        {
            $this->notifySubscriber($subscribe, $route, $monthAvgPrice);
        }
    }

    private function notifySubscriber(FlightAvgPriceSubscribe $subscribe, Route $route, ?float $monthAvgPrice)
    {
        $message = $this->buildMessage($subscribe, $route, $monthAvgPrice);
        try
        {
            $this->telegramClient->sendMessage($subscribe->getChat(), $message);
        }
        catch(\Exception $e)
        {
            $this->logger->error($e->getMessage());
        }
    }

    private function buildMessage(FlightAvgPriceSubscribe $subscribe, Route $route, ?float $monthAvgPrice)
    {
        $data = ['subscribe' => $subscribe, 'route' => $route, 'monthAvgPrice' => $monthAvgPrice];

        return $this->twig->render('notification/telegram/notify-about-flight-price-drop.twig', $data);
    }
}