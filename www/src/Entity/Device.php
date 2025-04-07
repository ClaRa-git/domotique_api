<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['device:read']]
)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['device:read', 'device_type:read', 'feature:read', 'planning:read', 'profile:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read', 'device_type:read', 'feature:read', 'planning:read', 'profile:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?string $label = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read', 'device_type:read', 'room:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read', 'device_type:read'])]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read', 'device_type:read'])]
    private ?string $reference = null;

    #[ORM\Column]
    #[Groups(['device:read', 'device_type:read', 'feature:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?bool $state = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[Groups(['device:read', 'feature:read', 'planning:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?DeviceType $deviceType = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[Groups(['device:read', 'setting:read'])]
    private ?Room $room = null;

    /**
     * @var Collection<int, Setting>
     */
    #[ORM\OneToMany(targetEntity: Setting::class, mappedBy: 'device')]
    #[Groups(['device:read', 'device_type:read', 'planning:read', 'room:read'])]
    private Collection $settings;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
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

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): static
    {
        $this->state = $state;

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
}
