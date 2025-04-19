<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\DeviceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DeviceTypeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['device_type:read']]
)]
class DeviceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['device_type:read', 'device:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device_type:read', 'device:read'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Device>
     */
    #[ORM\OneToMany(targetEntity: Device::class, mappedBy: 'deviceType')]
    private Collection $devices;

    #[ORM\ManyToOne(inversedBy: 'deviceTypes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['device:read'])]
    private ?Protocole $protocole = null;

    /**
     * @var Collection<int, Feature>
     */
    #[ORM\OneToMany(targetEntity: Feature::class, mappedBy: 'deviceType')]
    #[Groups(['device:read'])]
    private Collection $features;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
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
     * @return Collection<int, Device>
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): static
    {
        if (!$this->devices->contains($device)) {
            $this->devices->add($device);
            $device->setDeviceType($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): static
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getDeviceType() === $this) {
                $device->setDeviceType(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function getProtocole(): ?Protocole
    {
        return $this->protocole;
    }

    public function setProtocole(?Protocole $protocole): static
    {
        $this->protocole = $protocole;

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
            $feature->setDeviceType($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getDeviceType() === $this) {
                $feature->setDeviceType(null);
            }
        }

        return $this;
    }
}
