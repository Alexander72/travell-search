<?php


namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GeoApiController extends AbstractController
{
    private $cityRepository;

    public function __construct(
        CityRepository $cityRepository
    ) {
        $this->cityRepository = $cityRepository;
    }

    /**
     * @Route("/api/v1/geo/city", name="api_geo_city")
     * @param Request $request
     */
    public function index(Request $request)
    {
        $cities = $this->cityRepository->getLargeEuropeCities();
        $cities = array_map(function(City $city){return ['code' => $city->getCode(), 'name' => $city->getName()];}, $cities);
        return new JsonResponse($cities);
    }
}