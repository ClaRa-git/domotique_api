<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\CriteriaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CriteriaRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(),
        new Post(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['criteria:read']],
    denormalizationContext: ['groups' => ['criteria:write']],

)]
class Criteria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['criteria:read','vibe:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['criteria:read','criteria:write','vibe:read'])]
    private ?int $mood = null;

    #[ORM\Column]
    #[Groups(['criteria:read','criteria:write','vibe:read'])]
    private ?int $stress = null;

    #[ORM\Column]
    #[Groups(['criteria:read','criteria:write','vibe:read'])]
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