<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 17.03.19
 * Time: 0:44
 */

namespace App\Builders;


use App\Entity\LoadFlightsCommandState;
use App\Repository\LoadFlightsCommandStateRepository;
use Doctrine\ORM\EntityManagerInterface;

class FlightLoadStateBuilder
{
    /**
     * @var LoadFlightsCommandStateRepository
     */
    private $stateRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \DateTime
     */
    private $departureMonthFirstDay;

    /**
     * @var array
     */
    private $defaultOriginCities;

    /**
     * @var array
     */
    private $defaultDestinationCities;

    /**
     * @return \DateTime
     */
    public function getDepartureMonthFirstDay(): ?\DateTime
    {
        return $this->departureMonthFirstDay;
    }

    /**
     * @param \DateTime $departureMonthFirstDay
     *
     * @return FlightLoadStateBuilder
     */
    public function setDepartureMonthFirstDay(?\DateTime $departureMonthFirstDay): FlightLoadStateBuilder
    {
        $this->departureMonthFirstDay = $departureMonthFirstDay;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultOriginCities(): array
    {
        return $this->defaultOriginCities;
    }

    /**
     * @param array $defaultOriginCities
     *
     * @return FlightLoadStateBuilder
     */
    public function setDefaultOriginCities(array $defaultOriginCities): FlightLoadStateBuilder
    {
        $this->defaultOriginCities = $defaultOriginCities;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultDestinationCities(): array
    {
        return $this->defaultDestinationCities;
    }

    /**
     * @param array $defaultDestinationCities
     *
     * @return FlightLoadStateBuilder
     */
    public function setDefaultDestinationCities(array $defaultDestinationCities): FlightLoadStateBuilder
    {
        $this->defaultDestinationCities = $defaultDestinationCities;

        return $this;
    }

    /**
     * FlightLoadStateBuilder constructor.
     *
     * @param LoadFlightsCommandStateRepository $stateRepository
     * @param EntityManagerInterface            $em
     */
    public function __construct(
        LoadFlightsCommandStateRepository $stateRepository,
        EntityManagerInterface $em
    ) {
        $this->stateRepository = $stateRepository;
        $this->em = $em;
    }

    public function build(): LoadFlightsCommandState
    {
        $departureMonthFirstDay = $this->getDepartureMonthFirstDay();
        $state = $this->stateRepository->getLastState(false, $departureMonthFirstDay);
        if(!$state)
        {
            if($departureMonthFirstDay)
            {
                $state = new LoadFlightsCommandState();
                $state->setDepartMonthFirstDay($departureMonthFirstDay);
            }
            else
            {
                $firstDayOfCurrentMonth = new \DateTime('first day of this month');
                $state = $this->stateRepository->getLastState(true);
                if(!$state)
                {
                    $state = new LoadFlightsCommandState();
                    $state->setDepartMonthFirstDay($firstDayOfCurrentMonth);
                }
                else
                {
                    $departureMonthFirstDay = $state->getDepartMonthFirstDay();
                    $departureMonthFirstDay->modify('+1 month');
                    $departureMonthFirstDay = $firstDayOfCurrentMonth->diff($departureMonthFirstDay)->y >= 1 ? $firstDayOfCurrentMonth : $departureMonthFirstDay;

                    $state = new LoadFlightsCommandState();
                    $state->setDepartMonthFirstDay($departureMonthFirstDay);
                }
            }
        }

        if(!$state->getOrigins())
        {
            $state->setOrigins($this->getDefaultOriginCities());
        }

        if(!$state->getDestinations())
        {
            $state->setDestinations($this->getDefaultDestinationCities());
        }

        $this->em->persist($state);

        return $state;
    }
}