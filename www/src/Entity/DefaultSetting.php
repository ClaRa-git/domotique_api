<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\DefaultSettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DefaultSettingRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['default_setting:read']],
    denormalizationContext: ['groups' => ['default_setting:write']]
)]
class DefaultSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['default_setting:read', 'room:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['default_setting:read', 'default_setting:write', 'room:read', 'vibe:read'])]
    private ?string $value = null;

    #[ORM\ManyToOne(inversedBy: 'defaultSettings')]
    #[Groups(['default_setting:read', 'default_setting:write', 'room:read', 'vibe:read'])]
    private ?Feature $feature = null;

    #[ORM\ManyToOne(inversedBy: 'defaultSettings')]
    #[Groups(['default_setting:read', 'default_setting:write', 'room:read', 'vibe:read'])]
    private ?Device $device = null;

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
}
