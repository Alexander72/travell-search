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
     * @ORM\Column(type="json", nullable=true)
     */
    private $params;

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
     * @param \App\Entity\City $origin
     * @param \App\Entity\City $destination
     *
     * @throws \Exception
     */
    public function update(City $origin, City $destination)
    {
        $this->setStatus(LoadFlightsCommandState::STATUS_LOADING);
        $this->setOrigin($origin);
        $this->setDestination($destination);
        $this->setUpdated(new DateTime());
    }

    /**
     * @throws \Exception
     */
    public function finish()
    {
        $this->setUpdated(new DateTime());
        $this->setStatus(self::STATUS_FINISHED);
    }
}
