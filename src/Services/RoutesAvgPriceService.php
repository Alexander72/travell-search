<?php


namespace App\Services;


use App\Entity\City;
use App\Repository\RouteRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RoutesAvgPriceService
{
    private $routeRepository;

    private $cache;

    public function __construct(
        RouteRepository $routeRepository,
        CacheInterface $cache
    ) {
        $this->routeRepository = $routeRepository;
        $this->cache = $cache;
    }

    public function getRouteAvgPrice(string $period, ?City $origin, ?City $destination)
    {
        $cacheKey = $this->getCacheKey($period, $origin, $destination);
        $data = $this->cache->get($cacheKey, function(ItemInterface $item) use ($period, $origin, $destination) {
            $data = $this->routeRepository->getAvgPriceForRoute($period, $origin, $destination);
            $item->expiresAfter(60*60*24*3);
            return $data;
        });

        return $data;
    }

    /**
     * @param string $period
     * @param City $origin
     * @param City $destination
     * @return string
     */
    private function getCacheKey(string $period, ?City $origin, ?City $destination): string
    {
        $originCode = $origin ? $origin->getCode() : '';
        $destinationCode = $destination ? $destination->getCode() : '';

        return "route_{$period}_avg_price_statistic_{$originCode}_{$destinationCode}";
    }
}