<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 */
class Country
{
    const CONTINENT_EUROPE = 'europe';
    const CONTINENT_ASIA = 'asia';
    const CONTINENT_SOUTH_AMERICA = 'south_america';
    const CONTINENT_NORTH_AMERICA = 'north_america';
    const CONTINENT_AFRICA = 'africa';
    const CONTINENT_AUSTRALIA = 'australia';

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $continent;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(nullable=true, referencedColumnName="code")
     */
    private $capital;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * @param mixed $continent
     *
     * @return Country
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * @param mixed $capital
     *
     * @return Country
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

}
