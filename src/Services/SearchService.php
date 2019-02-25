<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 25.02.19
 * Time: 0:51
 */

namespace App\Services;


use App\Entity\City;
use App\Entity\Route;
use App\Entity\Trip;
use App\Exceptions\IncorrectTripOptionsException;
use App\Repository\RouteRepository;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class SearchService
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

    public function __construct(
        RouteRepository $routeRepository
    ) {
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
     * @return SearchService
     */
    public function setOptions(array $options): SearchService
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @throws IncorrectTripOptionsException
     */
    public function buildTrip(): ?Trip
    {
        $this->checkOptions();

        $startCity = $this->getOption('startCity');
        $startTime = $this->getOption('startTime');
        $finishTime = $this->getOption('finishTime');
        $maxPrice = $this->getOption('maxPrice');

        return $this->doBuildTrip();
    }

    private function doBuildTrip(?Trip $trip): ?Trip
    {
        /** @var Route $lastRoute */
        $lastRoute = $trip->getRoutes()->last();
        $lastRouteDepartureDay = $lastRoute->getDepartureDay();
        if($lastRouteDepartureDay)
        {
            $startCity = $lastRoute->getDestination();
            $nextRoutesStartTime = (clone $lastRouteDepartureDay)->modify('+1 day');
            $finishTime = $this->getOption('finishTime');
            $maxPrice = $this->getOption('maxPrice') - $trip->calculatePrice();
            $nextRoutes = $this->routeRepository->getRoutesFromCity($startCity, $nextRoutesStartTime, $finishTime, $maxPrice);

            foreach($nextRoutes as $nextRoute)
            {
                $newTrip = clone $trip;
                $newTrip->addRoute($nextRoute);
                $builtTrip = $this->doBuildTrip($newTrip);
                if($builtTrip)
                {
                    $this->builtTrip[] = $builtTrip;
                }
            }
        }
    }

    /**
     * @throws IncorrectTripOptionsException
     */
    private function checkOptions(): void
    {
        $validator = Validation::createValidator();
        $constraints = new Assert\Collection([
            'fields'           => [
                'startCity'          => [new Assert\NotBlank(), new Assert\Type(City::class)],
                'finishCity'         => new Assert\NotBlank(),
                'startTime'          => new Assert\NotBlank(),
                'finishTime'         => new Assert\NotBlank(),
                'maxPrice'           => new Assert\NotBlank(),
                'maxChanges'         => new Assert\NotBlank(),
                'requiredMiddleCity' => new Assert\NotBlank(),
            ],
            'allowExtraFields' => true,
        ]);
        $violations = $validator->validate($this->getOptions(), $constraints);

        if(count($violations) > 0)
        {
            $messages = array_map(function($violation) {
                return $violation->getMessage();
            }, $violations);
            throw new IncorrectTripOptionsException(implode('. ', $messages));
        }
    }
}
