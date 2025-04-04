<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['unit:read']]
)]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['unit:read', 'feature:read', 'device:read', 'planning:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['unit:read', 'feature:read', 'device:read', 'planning:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Feature>
     */
    #[ORM\OneToMany(targetEntity: Feature::class, mappedBy: 'unit')]
    #[Groups(['unit:read'])]
    private Collection $features;

    public function __construct()
    {
        $this->features = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(Feature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setUnit($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getUnit() === $this) {
                $feature->setUnit(null);
            }
        }

        return $this;
    }
}
