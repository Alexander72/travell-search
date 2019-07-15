<?php

namespace App\Entity;

use App\Entity\City;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FlightAvgPriceSubscribeRepository")
 */
class FlightAvgPriceSubscribe
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
    private $priceDropPercent;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(nullable=true, referencedColumnName="code")
     * @var City|null
     */
    private $origin;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\City")
     * @ORM\JoinColumn(nullable=true, referencedColumnName="code")
     * @var City|null
     */
    private $destination;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $chat;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \Datetime|null
     */
    private $from;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \Datetime|null
     */
    private $to;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPriceDropPercent(): ?int
    {
        return $this->priceDropPercent;
    }

    public function setPriceDropPercent(int $priceDropPercent): self
    {
        $this->priceDropPercent = $priceDropPercent;

        return $this;
    }

    /**
     * @return \App\Entity\City|null
     */
    public function getOrigin(): ?\App\Entity\City
    {
        return $this->origin;
    }

    /**
     * @param \App\Entity\City|null $origin
     *
     * @return FlightAvgPriceSubscribe
     */
    public function setOrigin(?\App\Entity\City $origin): FlightAvgPriceSubscribe
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return \App\Entity\City|null
     */
    public function getDestination(): ?\App\Entity\City
    {
        return $this->destination;
    }

    /**
     * @param \App\Entity\City|null $destination
     *
     * @return FlightAvgPriceSubscribe
     */
    public function setDestination(?\App\Entity\City $destination): FlightAvgPriceSubscribe
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return int
     */
    public function getChat(): int
    {
        return $this->chat;
    }

    /**
     * @param int $chat
     *
     * @return FlightAvgPriceSubscribe
     */
    public function setChat(int $chat): FlightAvgPriceSubscribe
    {
        $this->chat = $chat;

        return $this;
    }

    /**
     * @return \Datetime|null
     */
    public function getFrom(): ?\Datetime
    {
        return $this->from;
    }

    /**
     * @param \Datetime|null $from
     *
     * @return FlightAvgPriceSubscribe
     */
    public function setFrom(?\Datetime $from): FlightAvgPriceSubscribe
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return \Datetime|null
     */
    public function getTo(): ?\Datetime
    {
        return $this->to;
    }

    /**
     * @param \Datetime|null $to
     *
     * @return FlightAvgPriceSubscribe
     */
    public function setTo(?\Datetime $to): FlightAvgPriceSubscribe
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * @param int|null $price
     * @return FlightAvgPriceSubscribe
     */
    public function setPrice(?int $price): FlightAvgPriceSubscribe
    {
        $this->price = $price;

        return $this;
    }
}
