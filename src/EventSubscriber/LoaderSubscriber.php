<?php


namespace App\EventSubscriber;


use App\Events\NewRouteLoadedEvent;
use App\Repository\FlightAvgPriceSubscribeRepository;
use App\Services\RoutesAvgPriceService;
use App\Services\TelegramSubscribeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoaderSubscriber implements EventSubscriberInterface
{
    private $routesAvgPriceService;

    private $avgPriceSubscribeRepository;

    private $telegramSubscribeService;

    public function __construct(
        RoutesAvgPriceService $routesAvgPriceService,
        FlightAvgPriceSubscribeRepository $avgPriceSubscribeRepository,
        TelegramSubscribeService $telegramSubscribeService
    ) {
        $this->routesAvgPriceService = $routesAvgPriceService;
        $this->avgPriceSubscribeRepository = $avgPriceSubscribeRepository;
        $this->telegramSubscribeService = $telegramSubscribeService;
    }

    public static function getSubscribedEvents()
    {
        return [
            NewRouteLoadedEvent::NAME => 'onNewRouteLoaded',
        ];
    }

    public function onNewRouteLoaded(NewRouteLoadedEvent $event)
    {
        $route = $event->getRoute();
        $destination = $route->getDestination();
        $origin = $route->getOrigin();

        $monthAvgPrice = $this->routesAvgPriceService->getRouteAvgMonthPrice($route->getDepartureDay()->format('j'), $origin, $destination);
        if($monthAvgPrice)
        {
            $percent = round($route->getPrice() / $monthAvgPrice * 100);
            $subscribers = $this->avgPriceSubscribeRepository->getSubscribers($percent, $origin, $destination);
            $this->telegramSubscribeService->notify($subscribers, $route, $monthAvgPrice);
        }
    }

}