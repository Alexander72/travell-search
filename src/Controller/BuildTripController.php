<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26.02.19
 * Time: 11:30
 */

namespace App\Controller;


use App\Builders\TripBuilder;
use App\Repository\CityRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BuildTripController extends AbstractController
{
    /**
     * @var TripBuilder
     */
    private $tripBuilder;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    /**
     * BuildTripController constructor.
     *
     * @param TripBuilder    $tripBuilder
     * @param CityRepository $cityRepository
     */
    public function __construct(
        TripBuilder $tripBuilder,
        CityRepository $cityRepository
    ) {
        $this->tripBuilder = $tripBuilder;
        $this->cityRepository = $cityRepository;
    }

    /**
     * @Route("/buildTrip", name="buildTrip")
     */
    public function index(Request $request)
    {
        $originCity = $this->cityRepository->findOneBy(['code' => 'MOW']);
        $destinationCity = $this->cityRepository->findOneBy(['code' => 'AMS']);
        $options = [
            'startCity' => $originCity,
            'finishCity' => $destinationCity,
            'startTime' => new DateTime('2019-05-01 00:00:00'),
            'finishTime' => new DateTime('2019-05-08 00:00:00'),
            'maxPrice' => 11000,
            'maxChanges' => 5,
        ];

        $this->tripBuilder->setOptions($options);
        $trips = $this->tripBuilder->buildTrips();

        uasort($trips, function($trip1, $trip2){return $trip1->getPrice() <=> $trip2->getPrice();});

        return $this->render('foundedTrips.twig', ['trips' => $trips]);
    }
}