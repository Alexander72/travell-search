<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26.02.19
 * Time: 11:30
 */

namespace App\Controller;


use App\Builders\TripBuilder;
use App\Entity\City;
use App\Repository\CityRepository;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('startCity', EntityType::class, $this->getCityFieldOptions());
        $formBuilder->add('finishCity', EntityType::class, $this->getCityFieldOptions());
        $formBuilder->add('startTime', DateType::class, ['widget' => 'single_text']);
        $formBuilder->add('finishTime', DateType::class, ['widget' => 'single_text']);
        $formBuilder->add('maxPrice', IntegerType::class);
        $formBuilder->add('maxChanges', IntegerType::class);
        $formBuilder->add('search', SubmitType::class);
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $searchOptions = $form->getData();

            $this->tripBuilder->setOptions($searchOptions);
            $trips = $this->tripBuilder->buildTrips();

            uasort($trips, function($trip1, $trip2){return $trip1->getPrice() <=> $trip2->getPrice();});
            $trips = \array_slice($trips, 0, 50);

            return $this->render('foundedTrips.twig', ['trips' => $trips]);
        }

        return $this->render('buildTripForm.twig', ['form' => $form->createView()]);
    }

    public function foundedTrips(array $trips)
    {
        return $this->render('foundedTrips.twig', ['trips' => $trips]);
    }

    /**
     * @return array
     */
    private function getCityFieldOptions(): array
    {
        return [
            'class' => City::class,
            'choice_label' => 'name',
            'query_builder' => function(CityRepository $cityRepository) {
                return $cityRepository->getLargeEuropeCitiesQueryBuilder();
            }
        ];
    }
}