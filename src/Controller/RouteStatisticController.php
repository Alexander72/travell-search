<?php


namespace App\Controller;

use App\Repository\RouteRepository;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class RouteStatisticController extends AbstractController
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
    
    /**
     * @Route("/route/statistic", name="route_statistic")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        return $this->render('stat/route_statistic.html.twig');
    }

    /**
     * @Route("/api/v1/statistic/year", name="api_statistic_year")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function yearStatistic(Request $request)
    {
        $originCode = $request->query->get('origin');
        $destinationCode = $request->query->get('destination');

        $cacheKey = "route_year_avg_price_statistic_{$originCode}_{$destinationCode}";
        $data = $this->cache->get($cacheKey, function(ItemInterface $item) use ($originCode, $destinationCode) {
            $data = $this->routeRepository->getYearAvgPriceForRoute($originCode, $destinationCode);
            $data = array_map(function($priceData){return $priceData['price'];}, $data);
            $item->expiresAfter(24*60*60);
            return $data;
        });

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/v1/statistic/week", name="api_statistic_week")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function weekStatistic(Request $request)
    {
        $originCode = $request->query->get('origin');
        $destinationCode = $request->query->get('destination');

        $cacheKey = "route_week_avg_price_statistic_{$originCode}_{$destinationCode}";
        $data = $this->cache->get($cacheKey, function(ItemInterface $item) use ($originCode, $destinationCode) {
            $data = $this->routeRepository->getWeekAvgPriceForRoute($originCode, $destinationCode);
            $data = array_map(function($priceData){return $priceData['price'];}, $data);
            $item->expiresAfter(24*60*60);
            return $data;
        });

        return new JsonResponse($data);
    }
}