<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\FeatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FeatureRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['feature:read']]
)]
class Feature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['feature:read', 'device:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['feature:read', 'device:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'features')]
    #[Groups(['device:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?Unit $unit = null;

    /**
     * @var Collection<int, Setting>
     */
    #[ORM\OneToMany(targetEntity: Setting::class, mappedBy: 'feature')]
    #[Groups(['room:read'])]
    private Collection $settings;

    /**
     * @var Collection<int, DefaultSetting>
     */
    #[ORM\OneToMany(targetEntity: DefaultSetting::class, mappedBy: 'feature')]
    private Collection $defaultSettings;

    #[ORM\ManyToOne(inversedBy: 'features')]
    private ?DeviceType $deviceType = null;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
        $this->defaultSettings = new ArrayCollection();
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

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return Collection<int, Setting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function addSetting(Setting $setting): static
    {
        if (!$this->settings->contains($setting)) {
            $this->settings->add($setting);
            $setting->setFeature($this);
        }

        return $this;
    }

    public function removeSetting(Setting $setting): static
    {
        if ($this->settings->removeElement($setting)) {
            // set the owning side to null (unless already changed)
            if ($setting->getFeature() === $this) {
                $setting->setFeature(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    /**
     * @return Collection<int, DefaultSetting>
     */
    public function getDefaultSettings(): Collection
    {
        return $this->defaultSettings;
    }

    public function addDefaultSetting(DefaultSetting $defaultSetting): static
    {
        if (!$this->defaultSettings->contains($defaultSetting)) {
            $this->defaultSettings->add($defaultSetting);
            $defaultSetting->setFeature($this);
        }

        return $this;
    }

    public function removeDefaultSetting(DefaultSetting $defaultSetting): static
    {
        if ($this->defaultSettings->removeElement($defaultSetting)) {
            // set the owning side to null (unless already changed)
            if ($defaultSetting->getFeature() === $this) {
                $defaultSetting->setFeature(null);
            }
        }

        return $this;
    }

    public function getDeviceType(): ?DeviceType
    {
        return $this->deviceType;
    }

    public function setDeviceType(?DeviceType $deviceType): static
    {
        $this->deviceType = $deviceType;

        return $this;
    }
}
