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
        $this->id = null;
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
            $this->setPrice($this->getPrice() + $route->getPrice());
        }

        return $this;
    }

    public function removeRoute(Route $route): self
    {
        if ($this->routes->contains($route)) {
            $this->routes->removeElement($route);
            $this->setPrice($this->getPrice() - $route->getPrice());
        }

        return $this;
    }
}
