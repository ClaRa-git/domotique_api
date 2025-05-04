<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['room:read']]
)]
#[ApiFilter(
    ExistsFilter::class,
    properties: [
        'vibePlaying'
    ]
)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['room:read', 'device:read', 'planning:read', 'profile:read', 'setting:read', 'vibe:read', "vibe_playing:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['room:read', 'device:read', 'planning:read', 'profile:read', 'setting:read', 'vibe:read', 'vibe_playing:read'])]
    private ?string $label = null;

    /**
     * @var Collection<int, Device>
     */
    #[ORM\OneToMany(targetEntity: Device::class, mappedBy: 'room')]
    #[Groups(['room:read', 'planning:read', 'profile:read'])]
    private Collection $devices;

    /**
     * @var Collection<int, Planning>
     */
    #[ORM\ManyToMany(targetEntity: Planning::class, inversedBy: 'rooms')]
    #[Groups(['room:read'])]
    private Collection $plannings;

    #[ORM\Column(length: 255)]
    #[Groups(['room:read'])]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[Groups(['room:read'])]
    private ?VibePlaying $vibePlaying = null;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
        $this->plannings = new ArrayCollection();
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
            $device->setRoom($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): static
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getRoom() === $this) {
                $device->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Planning>
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): static
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings->add($planning);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        $this->plannings->removeElement($planning);

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function getVibePlaying(): ?VibePlaying
    {
        return $this->vibePlaying;
    }

    public function setVibePlaying(?VibePlaying $vibePlaying): static
    {
        $this->vibePlaying = $vibePlaying;

        return $this;
    }
}
