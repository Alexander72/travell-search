<?php


namespace App\Controller;

use App\Repository\CityRepository;
use App\Services\RoutesAvgPriceService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RouteStatisticController extends AbstractController
{
    private $cityRepository;

    private $avgPriceService;

    public function __construct(
        CityRepository $cityRepository,
        RoutesAvgPriceService $avgPriceService
    ) {
        $this->cityRepository = $cityRepository;
        $this->avgPriceService = $avgPriceService;
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
     * @Route("/api/v1/statistic/route_avg_price", name="route_avg_price_statistic")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function routeAvgPriceStatistic(Request $request)
    {
        $origin = $destination = null;

        $originCode = $request->query->get('origin');
        $destinationCode = $request->query->get('destination');
        $periodType = $request->query->get('periodType');

        if(!in_array($periodType, ['year', 'week']))
        {
            throw new NotFoundHttpException();
        }

        if($originCode)
        {
            $origin = $this->cityRepository->find($originCode);
            if(!$origin)
            {
                throw new NotFoundHttpException();
            }
        }

        if($destinationCode)
        {
            $destination = $this->cityRepository->find($destinationCode);
            if(!$destination)
            {
                throw new NotFoundHttpException();
            }
        }

        $data = $this->avgPriceService->getRouteAvgPrices($periodType, $origin, $destination);

        return new JsonResponse($data);
    }
}