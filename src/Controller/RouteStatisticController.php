<?php


namespace App\Controller;

use App\Repository\RouteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RouteStatisticController extends AbstractController
{
    private $routeRepository;

    public function __construct(
        RouteRepository $routeRepository
    ) {
        $this->routeRepository = $routeRepository;
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
        $data = $this->routeRepository->getYearAvgPriceForRoute($originCode, $destinationCode);
        $data = array_map(function($priceData){return $priceData['price'];}, $data);
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
        $data = $this->routeRepository->getWeekAvgPriceForRoute($originCode, $destinationCode);
        $data = array_map(function($priceData){return $priceData['price'];}, $data);
        return new JsonResponse($data);
    }
}