<?php


namespace App\Services;


use App\Entity\City;
use App\Repository\RouteRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RoutesAvgPriceService
{
    const PERIOD_YEAR = 'year';

    const PERIOD_WEEK = 'week';

    private $routeRepository;

    private $cache;

    public function __construct(
        RouteRepository $routeRepository,
        CacheInterface $cache
    ) {
        $this->routeRepository = $routeRepository;
        $this->cache = $cache;
    }

    public function getRouteAvgMonthPrice(int $month, ?City $origin, ?City $destination): ?float
    {
        $avgPrices = $this->getRouteAvgPrices(RoutesAvgPriceService::PERIOD_YEAR, $origin, $destination);
        $avgPrices = array_filter($avgPrices, function($priceInfo)use($month){return $priceInfo['period'] == $month;});
        $avgPrice = reset($avgPrices)['price'];

        return $avgPrice;
    }

    public function getRouteAvgPrices(string $period, ?City $origin, ?City $destination)
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