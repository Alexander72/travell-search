<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26.02.19
 * Time: 11:30
 */

namespace App\Controller;

use App\Entity\Trip;
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

            $startCities = $this->getStartCities($searchOptions);
            $finishCities = $this->getFinishCities($searchOptions);

            foreach($startCities as $startCity)
            {
                foreach($finishCities as $finishCity)
                {
                    $searchOptions['startCity'] = $startCity;
                    $searchOptions['finishCity'] = $finishCity;
                    $searchOptions['maxPrice'] = $this->getCheapestTripPrice($trips, $searchOptions['maxPrice']);
                    $this->tripBuilder->setOptions($searchOptions);
                    $trips = $trips + $this->tripBuilder->buildTrips();
                }
            }

            $viewData['trips'] = $this->postProcessTrips($trips);
        }

        $viewData['form'] = $form->createView();

        return $this->render('baseForm.twig', $viewData);
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

    /**
     * @param array $searchOptions
     * @return array
     */
    private function getStartCities(array $searchOptions): array
    {
        return $this->getCitiesForSearch($searchOptions, 'start');
    }

    /**
     * @param array $searchOptions
     * @return array
     */
    private function getFinishCities(array $searchOptions): array
    {
        return $this->getCitiesForSearch($searchOptions, 'finish');
    }

    /**
     * @param array $searchOptions
     * @param string $type
     * @return \App\Entity\City[]|array
     */
    private function getCitiesForSearch(array $searchOptions, string $type)
    {
        $countryField = $type == 'start' ? 'startCountry' : 'finishCountry';
        $cityField = $type == 'start' ? 'startCity' : 'finishCity';

        $cities = [];
        if(!empty($searchOptions[$cityField]))
        {
            $cities = [$searchOptions[$cityField]];
        }
        elseif($searchOptions[$countryField])
        {
            $cities = $this->cityRepository->getLargeEuropeCities($searchOptions[$countryField]);
        }

        return $cities;
    }

    /**
     * @param Trip[] $trips
     * @param int $defaultPrice
     * @return int
     */
    private function getCheapestTripPrice(array $trips, int $defaultPrice)
    {
        if(!$trips)
        {
            return $defaultPrice;
        }

        $cheapestTripPrice = $defaultPrice;

        foreach($trips as $trip)
        {
            if($cheapestTripPrice > $trip->getPrice())
            {
                $cheapestTripPrice = $trip->getPrice();
            }
        }

        return $cheapestTripPrice;
    }
}