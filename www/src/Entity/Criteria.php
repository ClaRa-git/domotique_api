<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CriteriaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CriteriaRepository::class)]
#[ApiResource]
class Criteria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $mood = null;

    #[ORM\Column]
    private ?int $stress = null;

    #[ORM\Column]
    private ?int $tone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMood(): ?int
    {
        return $this->mood;
    }

    public function setMood(int $mood): static
    {
        $this->mood = $mood;

        return $this;
    }

    public function getStress(): ?int
    {
        return $this->stress;
    }

    public function setStress(int $stress): static
    {
        $this->stress = $stress;

        return $this;
    }

    public function getTone(): ?int
    {
        return $this->tone;
    }

    public function setTone(int $tone): static
    {
        $this->tone = $tone;

        return $this;
    }
}
