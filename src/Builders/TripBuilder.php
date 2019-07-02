<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 25.02.19
 * Time: 0:51
 */

namespace App\Builders;


use App\Entity\City;
use App\Entity\Route;
use App\Entity\Trip;
use App\Exceptions\IncorrectTripOptionsException;
use App\Repository\RouteRepository;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class TripBuilder
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var RouteRepository
     */
    private $routeRepository;

    /**
     * @var array
     */
    private $builtTrip = [];

    /**
     * @var array
     */
    private $cityMinPriceMap = [];

    private $startAt;

    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $optionMane
     *
     * @return mixed
     */
    public function getOption(string $optionMane)
    {
        return $this->options[$optionMane] ?? null;
    }

    /**
     * @param array $options
     *
     * @return TripBuilder
     */
    public function setOptions(array $options): TripBuilder
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string $option
     * @param $value
     * @return TripBuilder
     */
    public function setOption(string $option, $value): TripBuilder
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * @return Trip[]
     * @throws IncorrectTripOptionsException
     */
    public function buildTrips(): array
    {
        $this->checkOptions();

        $trip = new Trip();

        \ini_set('memory_limit', '512M');

        $cheapestDirectRoute = $this->routeRepository->getCheapestDirectRoute(
            $this->getOption('startCity'),
            $this->getOption('finishCity'),
            $this->getOption('startTime'),
            $this->getOption('finishTime'),
            $this->getOption('maxAge')
        );

        $maxPrice = $this->getOption('maxPrice');
        if($cheapestDirectRoute)
        {
            $maxPrice = min($this->getOption('maxPrice'), $cheapestDirectRoute->getPrice());
            $this->setOption('maxPrice', $maxPrice);
        }

        $this->routeRepository->preloadRoutes(
            $this->getOption('startTime'),
            $this->getOption('finishTime'),
            $maxPrice,
            $this->getOption('maxAge')
        );

        $this->startAt = time();
        $this->doBuildTrips($trip);

        $this->routeRepository->resetPreloadedRoutes();

        return $this->builtTrip;
    }

    /**
     * Функция пытается достроить маршрут, пока не будет доступных маршрутов из города, посещенного последним
     *
     * @param Trip  $trip  текущее маршрут
     */
    private function doBuildTrips(Trip $trip)
    {
        if(time() - $this->startAt > 20)
        {
            return;
        }

        $this->setLastCityInTripVisited($trip);
        $routes = $this->getAvailableRoutesForTrip($trip);
        foreach($routes as $route)
        {
            $tripFork = clone $trip;

            $tripFork->addRoute($route);

            if($this->isFinish($route->getDestination()))
            {
                $this->finalizeTrip($tripFork);
                continue;
            }

            if($this->isItPossibleToReduceCityMinPrice($trip, $route->getDestination()))
            {
                continue;
            }

            $this->doBuildTrips($tripFork);
        }
    }

    /**
     * Возвращает маршруты доступные из города, который был посещен последним в рамках путешествия.
     *
     * @param Trip  $trip
     *
     * @return Route[]
     */
    private function getAvailableRoutesForTrip(Trip $trip): array
    {
        $routesCount = count($trip->getRoutes());
        if(!$routesCount)
        {
            $params = [
                $this->getOption('startCity'),
                $this->getOption('startTime'),
                $this->getOption('finishTime'),
                $this->getOption('maxPrice'),
            ];
        }
        else
        {
            if($routesCount > $this->getOption('maxChanges'))
            {
                return [];
            }

            /** @var Route $lastRoute */
            $lastRoute = $trip->getRoutes()->last();
            $params = [
                $lastRoute->getDestination(),
                (clone $lastRoute->getDepartureDay())->modify('+1 day'),
                $this->getOption('finishTime'),
                $this->getOption('maxPrice') - $trip->getPrice(),
            ];
        }

        return $this->routeRepository->getRoutesFromCity(...$params);
    }

    /**
     * @throws IncorrectTripOptionsException
     */
    private function checkOptions(): void
    {
        $validator = Validation::createValidator();
        $constraints = new Assert\Collection([
            'fields'           => [
                'startCity'          => new Assert\NotBlank(),
                'finishCity'         => new Assert\NotBlank(),
                'startTime'          => new Assert\NotBlank(),
                'finishTime'         => new Assert\NotBlank(),
                'maxPrice'           => new Assert\NotBlank(),
                //'maxChanges'         => new Assert\NotBlank(),
                //'requiredMiddleCity' => new Assert\NotBlank(),
            ],
            'allowExtraFields' => true,
        ]);
        /** @var ConstraintViolationListInterface $violations */
        $violations = $validator->validate($this->getOptions(), $constraints);

        if($violations->count() > 0)
        {
            $messages = [];
            foreach($violations as $violation)
            {
                $messages[] = $violation->getMessage();
            }
            throw new IncorrectTripOptionsException(implode(' ', $messages));
        }
    }

    /**
     * @param City $city
     *
     * @return bool
     */
    private function isFinish(City $city): bool
    {
        return $city->getCode() == $this->getOption('finishCity')->getCode();
    }

    /**
     * @param Trip $tripFork
     *
     * @return Trip
     */
    private function finalizeTrip(Trip $tripFork): Trip
    {
        return $this->builtTrip[] = $tripFork;
    }

    /**
     * @param Trip  $trip
     * @param City $city
     *
     * @return bool
     */
    private function isItPossibleToReduceCityMinPrice(Trip $trip, City $city): bool
    {
        return isset($this->cityMinPriceMap[$city->getCode()]) && $this->cityMinPriceMap[$city->getCode()] < $trip->getPrice();
    }

    /**
     * @param Trip $trip
     */
    private function setLastCityInTripVisited(Trip $trip)
    {
        if($trip->getRoutes()->count())
        {
            $this->cityMinPriceMap[$trip->getRoutes()->last()->getDestination()->getCode()] = $trip->getPrice();
        }
    }
}
