<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26.02.19
 * Time: 11:30
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Builders\TripBuilder;
use App\Form\TripsSearchForm;
use App\Repository\CityRepository;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/find-trips", name="searchForm")
     */
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $form = $this->createForm(TripsSearchForm::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $searchOptions = $form->getData();
            $trips = [];

            $startCities = [$searchOptions['startCity']];
            $finishCities = [$searchOptions['finishCity']];
            if(!empty($searchOptions['startCountry']))
            {
                $startCities = $this->cityRepository->getLargeEuropeCities($searchOptions['startCountry']);
            }
            if(!empty($searchOptions['finishCountry']))
            {
                $finishCities = $this->cityRepository->getLargeEuropeCities($searchOptions['finishCountry']);
            }

            foreach($startCities as $startCity)
            {
                foreach($finishCities as $finishCity)
                {
                    $searchOptions['startCity'] = $startCity;
                    $searchOptions['finishCity'] = $finishCity;
                    $this->tripBuilder->setOptions($searchOptions);
                    $trips = $trips + $this->tripBuilder->buildTrips();
                }
            }

            $trips = $this->postProcessTrips($trips);

            return $this->render('foundedTrips.twig', ['trips' => $trips]);
        }

        return $this->render('buildTripForm.twig', ['form' => $form->createView()]);
    }

    /**
     * @param array $trips
     *
     * @return array
     */
    private function postProcessTrips(array $trips): array
    {
        uasort($trips, function($trip1, $trip2) {
            return $trip1->getPrice() <=> $trip2->getPrice();
        });
        $trips = \array_slice($trips, 0, 50);

        return $trips;
    }
}