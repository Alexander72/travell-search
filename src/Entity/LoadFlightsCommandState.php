<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use \App\Entity\City;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoadFlightsCommandStateRepository")
 * @ORM\Table(name="load_status")
 */
class LoadFlightsCommandState
{
    const STATUS_PENDING = 'pending';
    const STATUS_LOADING = 'loading';
    const STATUS_FINISHED = 'finished';

    const TYPE = 'LoadMultipleFlightsCommand';
    const DESTINATIONS_PARAM = 'destinations';
    const ORIGINS_PARAM = 'origins';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(referencedColumnName="code", nullable=true)
     */
    private $origin;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(referencedColumnName="code", nullable=true)
     */
    private $destination;

    /**
     * @ORM\Column(type="string")
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(type="string")
     */
    private $type = self::TYPE;

    /**
     * @ORM\Column(type="date")
     */
    private $departMonthFirstDay;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $params;

    /**
     * @ORM\Column(type="float")
     */
    private $percent;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $memoryUsage;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getOrigin(): ?City
    {
        return $this->origin;
    }

    public function setOrigin(?City $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     *
     * @return LoadFlightsCommandState
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return LoadFlightsCommandState
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParam(string $name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * @param string $name
     * @param        $param
     *
     * @return $this
     */
    public function setParam(string $name, $param)
    {
        $this->params[$name] = $param;

        return $this;
    }

    /**
     * @param array $originCities
     *
     * @return $this
     */
    public function setOrigins(array $originCities)
    {
        $this->setParam(self::ORIGINS_PARAM, array_map(function($city){return $city->getCode();}, $originCities));

        return $this;
    }

    /**
     * @param array $destinationCities
     *
     * @return $this
     */
    public function setDestinations(array $destinationCities)
    {
        $this->setParam(self::DESTINATIONS_PARAM, array_map(function($city){return $city->getCode();}, $destinationCities));

        return $this;
    }

    public function getOrigins()
    {
        return $this->getParam(self::ORIGINS_PARAM) ?? [];
    }

    public function getDestinations()
    {
        return $this->getParam(self::DESTINATIONS_PARAM) ?? [];
    }

    /**
     * @param mixed $params
     *
     * @return LoadFlightsCommandState
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return LoadFlightsCommandState
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDepartMonthFirstDay()
    {
        return $this->departMonthFirstDay;
    }


    /**
     * @param mixed $departMonthFirstDay
     *
     * @return LoadFlightsCommandState
     */
    public function setDepartMonthFirstDay($departMonthFirstDay)
    {
        $this->departMonthFirstDay = $departMonthFirstDay;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @return mixed
     */
    public function getMemoryUsage()
    {
        return $this->memoryUsage;
    }

    /**
     * @param mixed $memoryUsage
     *
     * @return LoadFlightsCommandState
     */
    public function setMemoryUsage(int $memoryUsage)
    {
        $this->memoryUsage = $memoryUsage;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function __construct()
    {
        $this->setStartedAt(new DateTime());
        $this->setMemoryUsage(\memory_get_usage());
    }

    /**
     * @param \App\Entity\City $origin
     * @param \App\Entity\City $destination
     *
     * @throws \Exception
     */
    public function update(City $origin, City $destination)
    {
        $this->setMemoryUsage(\memory_get_usage());
        $this->setStatus(LoadFlightsCommandState::STATUS_LOADING);
        $this->setOrigin($origin);
        $this->setDestination($destination);
        $this->setUpdated(new DateTime());
        $this->updatePercent($origin->getCode(), $destination->getCode());
    }

    /**
     * @throws \Exception
     */
    public function finish()
    {
        $this->percent = 100;
        $this->setUpdated(new DateTime());
        $this->setStatus(self::STATUS_FINISHED);
    }

    private function updatePercent(string $originCode, string $destinationCode)
    {
        $origins = $this->getOrigins();
        $destinations = $this->getDestinations();
        if(!$origins || !\is_array($origins))
        {
            throw new \Exception('origins parameter is not set.');
        }
        if(!$destinations || !\is_array($destinations))
        {
            throw new \Exception('destinations parameter is not set.');
        }

        $originCitiesCount = count($origins);
        $total = $originCitiesCount * $originCitiesCount;
        $passed = array_search($originCode, $origins) * $originCitiesCount + array_search($destinationCode, $destinations) + 1;

        $this->percent = \round($passed / $total * 100, 2);
    }
}
