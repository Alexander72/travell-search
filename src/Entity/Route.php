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
     * @ORM\JoinColumn(nullable=false)
     */
    private $origin;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(nullable=false)
     */
    private $destination;

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Route
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }
}
