<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Entity\Route;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TripRepository")
 */
class Trip
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Route")
     */
    private $routes;

    public function __construct()
    {
        $this->routes = new ArrayCollection();
    }

    public function __clone()
    {
        $this->routes = new ArrayCollection($this->routes->toArray());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function calculatePrice(): int
    {
        return array_reduce($this->getRoutes(), function($sum, Route $route){return $sum + $route->getPrice();});
    }

    /**
     * @return Collection|Route[]
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function addRoute(Route $route): self
    {
        if (!$this->routes->contains($route)) {
            $this->routes[] = $route;
        }

        return $this;
    }

    public function removeRoute(Route $route): self
    {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
        }

        return $this;
    }
}
