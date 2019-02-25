<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 25.02.19
 * Time: 0:51
 */

namespace App\Services;


use App\Entity\City;
use App\Repository\RouteRepository;

class SearchService
{
    /**
     * @var City
     */
    private $startCity;
    /**
     * @var City
     */
    private $finishCity;
    /**
     * @var City
     */
    private $requiredMiddleCity;
    /**
     * @var \DateTime
     */
    private $startTime;
    /**
     * @var \DateTime
     */
    private $finishTime;

    /**
     * @var RouteRepository
     */
    private $routeRepository;

    public function __construct(
        RouteRepository $routeRepository
    ) {
        $this->routeRepository = $routeRepository;
    }

    public function setStartCity(City $city)
    {
        $this->startCity = $city;
    }

    public function setFinishCity(City $city)
    {
        $this->finishCity = $city;
    }

    public function setRequiredMiddleCity(City $city)
    {
        $this->requiredMiddleCity = $city;
    }

    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;
    }

    public function setFinishTime(\DateTime $finishTime)
    {
        $this->finishTime = $finishTime;
    }

    /**
     * @return City
     */
    public function getStartCity(): City
    {
        return $this->startCity;
    }

    /**
     * @return City
     */
    public function getFinishCity(): City
    {
        return $this->finishCity;
    }

    /**
     * @return City
     */
    public function getRequiredMiddleCity(): City
    {
        return $this->requiredMiddleCity;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    /**
     * @return \DateTime
     */
    public function getFinishTime(): \DateTime
    {
        return $this->finishTime;
    }

    public function buildTrip()
    {

    }
}
