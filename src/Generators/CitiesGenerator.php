<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 05.03.19
 * Time: 0:47
 */

namespace App\Generators;


use App\Entity\City;
use App\Entity\LoadFlightsCommandState;

class CitiesGenerator
{
    private $iterator;

    /**
     * CitiesGenerator constructor.
     *
     * @param array $cities
     * @param City  $currentCity
     *
     * @throws \Exception
     */
    public function __construct(array $cities, ?City $currentCity)
    {
        $this->iterator = $this->createForwardedCitiesIteratorByState($cities, $currentCity);
    }

    public function get(): \Generator
    {
        while($this->iterator->current())
        {
            yield $this->iterator->current()->getCode() => $this->iterator->current();
            $this->iterator->next();
        }
    }

    public function reset()
    {
        $this->iterator->rewind();
    }


    /**
     * @param array $cities
     * @param City  $currentCity
     *
     * @return \ArrayIterator
     * @throws \Exception
     */
    private function createForwardedCitiesIteratorByState(array $cities, ?City $currentCity): \ArrayIterator
    {
        $iterator = new \ArrayIterator($cities);

        if($currentCity)
        {
            while($iterator->current() && $iterator->current()->getCode() != $currentCity->getCode())
            {
                $iterator->next();
            }

            if(!$iterator->current())
            {
                throw new \Exception("City {$currentCity->getCode()} not found in city array. Unable to forward iterator.");
            }
        }

        return $iterator;
    }
}