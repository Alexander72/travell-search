<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchRequestRepository")
 */
class SearchRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysDurationMin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $daysDurationMax;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTimeInterface $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTimeInterface $dateTo): self
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    public function getDaysDurationMin(): ?int
    {
        return $this->daysDurationMin;
    }

    public function setDaysDurationMin(?int $daysDurationMin): self
    {
        $this->daysDurationMin = $daysDurationMin;

        return $this;
    }

    public function getDaysDurationMax(): ?int
    {
        return $this->daysDurationMax;
    }

    public function setDaysDurationMax(?int $daysDurationMax): self
    {
        $this->daysDurationMax = $daysDurationMax;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
