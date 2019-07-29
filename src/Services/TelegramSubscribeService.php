<?php


namespace App\Services;


use App\Entity\Route;
use App\Entity\FlightAvgPriceSubscribe;
use App\Entity\TelegramMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Client;
use Twig\Environment;

class TelegramSubscribeService
{
    private $telegramClient;

    private $twig;

    private $logger;

    private $entityManager;

    public function __construct(
        Client $telegramClient,
        Environment $twig,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->telegramClient = $telegramClient;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
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
        $text = $this->buildMessage($subscribe, $route, $monthAvgPrice);
        try
        {
            $message = new TelegramMessage();
            $message->setDirection(TelegramMessage::DIRECTION_OUTCOME);
            $message->setText($text);
            $message->setDate(new \DateTime());
            $message->setChatId($subscribe->getChat());

            $this->entityManager->persist($message);

            $this->telegramClient->sendMessage($subscribe->getChat(), $text);
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