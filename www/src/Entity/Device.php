<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['device:read']]
)]
#[ApiFilter(
    ExistsFilter::class,
    properties: [
        'room'
    ]
)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['default_setting:read', 'device:read','room:read','setting:read','vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['default_setting:read', 'device:read','room:read','setting:read','vibe:read'])]
    private ?string $label = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read','room:read','setting:read','vibe:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read'])]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read'])]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[Groups(['device:read','room:read','vibe:read'])]
    private ?DeviceType $deviceType = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[Groups(['device:read','setting:read','vibe:read'])]
    private ?Room $room = null;

    /**
     * @var Collection<int, Setting>
     */
    #[ORM\OneToMany(targetEntity: Setting::class, mappedBy: 'device')]
    private Collection $settings;

    /**
     * @var Collection<int, DefaultSetting>
     */
    #[ORM\OneToMany(targetEntity: DefaultSetting::class, mappedBy: 'device')]
    private Collection $defaultSettings;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

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
            $setting->setDevice($this);
        }

        return $this;
    }

    public function removeSetting(Setting $setting): static
    {
        if ($this->settings->removeElement($setting)) {
            // set the owning side to null (unless already changed)
            if ($setting->getDevice() === $this) {
                $setting->setDevice(null);
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
            $defaultSetting->setDevice($this);
        }

        return $this;
    }

    public function removeDefaultSetting(DefaultSetting $defaultSetting): static
    {
        if ($this->defaultSettings->removeElement($defaultSetting)) {
            // set the owning side to null (unless already changed)
            if ($defaultSetting->getDevice() === $this) {
                $defaultSetting->setDevice(null);
            }
        }

        return $this;
    }
}
