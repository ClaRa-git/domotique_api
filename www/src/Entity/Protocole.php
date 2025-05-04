<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ProtocoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProtocoleRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['protocole:read']]
)]
class Protocole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['protocole:read', 'device:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['protocole:read', 'device:read', 'room:read', 'setting:read', 'vibe:read'])]
    private ?string $label = null;

    /**
     * @var Collection<int, DeviceType>
     */
    #[ORM\OneToMany(targetEntity: DeviceType::class, mappedBy: 'protocole')]
    private Collection $deviceTypes;

    public function __construct()
    {
        $this->deviceTypes = new ArrayCollection();
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
     * @return Collection<int, DeviceType>
     */
    public function getDeviceTypes(): Collection
    {
        return $this->deviceTypes;
    }

    public function addDeviceType(DeviceType $deviceType): static
    {
        if (!$this->deviceTypes->contains($deviceType)) {
            $this->deviceTypes->add($deviceType);
            $deviceType->setProtocole($this);
        }

        return $this;
    }

    public function removeDeviceType(DeviceType $deviceType): static
    {
        if ($this->deviceTypes->removeElement($deviceType)) {
            // set the owning side to null (unless already changed)
            if ($deviceType->getProtocole() === $this) {
                $deviceType->setProtocole(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
