<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(),
        new Post(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['setting:read']],
    denormalizationContext: ['groups' => ['setting:write']]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'device.id' => 'exact',
    ]
)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['setting:read', 'device:read', 'device_type:read', 'feature:read', 'planning:read', 'room:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['setting:read', 'setting:write', 'device:read', 'device_type:read', 'feature:read', 'planning:read', 'room:read', 'vibe:read'])]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'settings')]
    #[Groups(['setting:read', 'setting:write', 'device:read', 'device_type:read', 'planning:read', 'room:read', 'vibe:read'])]
    private ?Feature $feature = null;

    #[ORM\ManyToOne(inversedBy: 'settings')]
    #[Groups(['setting:read', 'setting:write', 'feature:read', 'vibe:read'])]
    private ?Device $device = null;

    #[ORM\ManyToOne(inversedBy: 'settings')]
    #[Groups(['setting:read', 'setting:write', 'device:read', 'room:read'])]
    private ?Vibe $vibe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getFeature(): ?Feature
    {
        return $this->feature;
    }

    public function setFeature(?Feature $feature): static
    {
        $this->feature = $feature;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): static
    {
        $this->device = $device;

        return $this;
    }

    public function getVibe(): ?Vibe
    {
        return $this->vibe;
    }

    public function setVibe(?Vibe $vibe): static
    {
        $this->vibe = $vibe;

        return $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
