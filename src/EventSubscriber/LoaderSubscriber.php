<?php


namespace App\EventSubscriber;


use App\Events\NewRouteLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoaderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            NewRouteLoadedEvent::NAME => 'onNewRouteLoaded',
        ];
    }

    public function onNewRouteLoaded(NewRouteLoadedEvent $event)
    {
        $route = $event->getRoute();
        echo $route->getPrice()."\n";
    }

}