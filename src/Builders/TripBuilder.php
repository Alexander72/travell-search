<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 25.02.19
 * Time: 0:51
 */

namespace App\Builders;


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

    private $startAt;

    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
        $this->startAt = time();
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
     * @return Trip[]
     * @throws IncorrectTripOptionsException
     */
    public function buildTrips(): array
    {
        $this->checkOptions();

        $trip = new Trip();

        $this->routeRepository->preloadRoutes($this->getOption('startTime'), $this->getOption('finishTime'), $this->getOption('maxPrice'));

        $this->doBuildTrips($trip);

        return $this->builtTrip;
    }

    /**
     * Функция пытается достроить маршрут, пока не будет доступных маршрутов из города, посещенного последним
     *
     * Не отмечаем города как посещенные, так как допускается посещение одного города дважды,
     * а по одному и тому же пути мы не пройдем дважды,
     * так как функция нахождения путей монотонно возрастает по времени
     *
     * @param Trip  $trip  текущее маршрут
     */
    private function doBuildTrips(Trip $trip)
    {
        if(time() - $this->startAt > 20)
        {
            return;
        }
        $routes = $this->getAvailableRoutesForTrip($trip);
        foreach($routes as $route)
        {
            $tripFork = clone $trip;


            $tripFork->addRoute($route);

            if($route->getDestination()->getCode() == $this->getOption('finishCity')->getCode())
            {
                $this->builtTrip[]= $tripFork;
                continue;
            }

            $this->doBuildTrips($tripFork, $route);
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

        if(count($violations) > 0)
        {
            $messages = [];
            foreach($violations as $violation)
            {
                $messages[] = $violation->getMessage();
            }
            throw new IncorrectTripOptionsException(implode(' ', $messages));
        }
    }
}
