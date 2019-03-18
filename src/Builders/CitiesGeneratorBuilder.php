<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 17.03.19
 * Time: 2:04
 */

namespace App\Builders;


use App\Entity\LoadFlightsCommandState;
use App\Generators\CitiesGenerator;
use App\Repository\CityRepository;

class CitiesGeneratorBuilder
{
    /**
     * @var LoadFlightsCommandState
     */
    private $state;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    /**
     * @return LoadFlightsCommandState
     */
    public function getState(): LoadFlightsCommandState
    {
        return $this->state;
    }

    /**
     * @param LoadFlightsCommandState $state
     *
     * @return CitiesGeneratorBuilder
     */
    public function setState(LoadFlightsCommandState $state): CitiesGeneratorBuilder
    {
        $this->state = $state;

        return $this;
    }

    /**
     * CitiesGeneratorBuilder constructor.
     *
     * @param CityRepository $cityRepository
     */
    public function __construct(
        CityRepository $cityRepository
    ) {
        $this->cityRepository = $cityRepository;
    }

    /**
     * @return CitiesGenerator
     * @throws \Exception
     */
    public function buildOriginsGenerator()
    {
        $state = $this->getState();
        $cities = \array_map(function($cityCode){return $this->cityRepository->find($cityCode);}, $state->getOrigins());

        return new CitiesGenerator($cities, $state->getOrigin());
    }

    /**
     * @return CitiesGenerator
     * @throws \Exception
     */
    public function buildDestinationsGenerator()
    {
        $state = $this->getState();
        $cities = \array_map(function($cityCode){return $this->cityRepository->find($cityCode);}, $state->getDestinations());

        return new CitiesGenerator($cities, $state->getDestination());
    }
}
