<?php


namespace App\Events;


use App\Entity\Route;
use Symfony\Component\EventDispatcher\Event;

class NewRouteLoadedEvent extends Event
{
    const NAME = 'loader.new_route';

    private $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }
}