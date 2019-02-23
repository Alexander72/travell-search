<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Entity\City;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RouteRepository")
 */
class Route
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="code")
     */
    private $origin;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(nullable=false, referencedColumnName="code")
     */
    private $destination;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $cost;

    /**
     * @ORM\Column(nullable=true, type="date")
     * @var \DateTime|null
     */
    private $departureDay;

    /**
     * @ORM\Column(nullable=true, type="time")
     * @var \DateTime|null
     */
    private $departureTime;

    /**
     * @ORM\Column(nullable=true, type="datetime")
     * @var \DateTime|null
     */
    private $foundAt;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $savedAt;

    /**
     * @ORM\Column(nullable=true, type="integer")
     * @var int|null
     */
    private $duration;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     *
     * @return Route
     */
    public function setOrigin($origin)
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
     * @return Route
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return int
     */
    public function getCost(): int
    {
        return $this->cost;
    }

    /**
     * @param int $cost
     *
     * @return Route
     */
    public function setCost(int $cost): Route
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDepartureDay(): ?\DateTime
    {
        return $this->departureDay;
    }

    /**
     * @param \DateTime|null $departureDay
     *
     * @return Route
     */
    public function setDepartureDay(?\DateTime $departureDay): Route
    {
        $this->departureDay = $departureDay;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDepartureTime(): ?\DateTime
    {
        return $this->departureTime;
    }

    /**
     * @param \DateTime|null $departureTime
     *
     * @return Route
     */
    public function setDepartureTime(?\DateTime $departureTime): Route
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getFoundAt(): ?\DateTime
    {
        return $this->foundAt;
    }

    /**
     * @param \DateTime|null $foundAt
     *
     * @return Route
     */
    public function setFoundAt(?\DateTime $foundAt): Route
    {
        $this->foundAt = $foundAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSavedAt(): \DateTime
    {
        return $this->savedAt;
    }

    /**
     * @param \DateTime $savedAt
     *
     * @return Route
     */
    public function setSavedAt(\DateTime $savedAt): Route
    {
        $this->savedAt = $savedAt;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int|null $duration
     *
     * @return Route
     */
    public function setDuration(?int $duration): Route
    {
        $this->duration = $duration;

        return $this;
    }
}
