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
use App\Repository\VibeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VibeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(),
        new Post(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['vibe:read']],
    denormalizationContext: ['groups' => ['vibe:write']]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'profile.id' => 'exact'
    ]
)]
class Vibe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vibe:read', 'device:read', 'icon:read', 'planning:read', 'playlist:read', 'profile:read', 'room:read', 'setting:read', 'vibe_playing:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vibe:read', 'vibe:write', 'device:read', 'icon:read', 'planning:read', 'playlist:read', 'profile:read', 'room:read', 'setting:read', 'vibe_playing:read'])]
    private ?string $label = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['vibe:read', 'vibe:write', 'profile:read'])]
    private ?Criteria $criteria = null;

    #[ORM\ManyToOne(inversedBy: 'vibes')]
    #[Groups(['vibe:read', 'vibe:write'])]
    private ?Playlist $playlist = null;

    #[ORM\ManyToOne(inversedBy: 'vibes')]
    #[Groups(['vibe:read', 'vibe:write'])]
    private ?Profile $profile = null;

    /**
     * @var Collection<int, Planning>
     */
    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'vibe')]
    #[Groups(['vibe:read', 'vibe:write', 'profile:read'])]
    private Collection $plannings;

    /**
     * @var Collection<int, Setting>
     */
    #[ORM\OneToMany(targetEntity: Setting::class, mappedBy: 'vibe')]
    #[Groups(['vibe:read', 'vibe:write'])]
    private Collection $settings;

    #[ORM\ManyToOne(inversedBy: 'vibes')]
    #[Groups(['vibe:read', 'vibe:write', 'planning:read'])]
    private ?Icon $icon = null;

    /**
     * @var Collection<int, VibePlaying>
     */
    #[ORM\OneToMany(targetEntity: VibePlaying::class, mappedBy: 'vibe')]
    private Collection $vibePlayings;

    public function __construct()
    {
        $this->plannings = new ArrayCollection();
        $this->settings = new ArrayCollection();
        $this->vibePlayings = new ArrayCollection();
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

    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(?Criteria $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getPlaylist(): ?Playlist
    {
        return $this->playlist;
    }

    public function setPlaylist(?Playlist $playlist): static
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

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
            $planning->setVibe($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getVibe() === $this) {
                $planning->setVibe(null);
            }
        }

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
            $setting->setVibe($this);
        }

        return $this;
    }

    public function removeSetting(Setting $setting): static
    {
        if ($this->settings->removeElement($setting)) {
            // set the owning side to null (unless already changed)
            if ($setting->getVibe() === $this) {
                $setting->setVibe(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    public function setIcon(?Icon $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, VibePlaying>
     */
    public function getVibePlayings(): Collection
    {
        return $this->vibePlayings;
    }

    public function addVibePlaying(VibePlaying $vibePlaying): static
    {
        if (!$this->vibePlayings->contains($vibePlaying)) {
            $this->vibePlayings->add($vibePlaying);
            $vibePlaying->setVibe($this);
        }

        return $this;
    }

    public function removeVibePlaying(VibePlaying $vibePlaying): static
    {
        if ($this->vibePlayings->removeElement($vibePlaying)) {
            // set the owning side to null (unless already changed)
            if ($vibePlaying->getVibe() === $this) {
                $vibePlaying->setVibe(null);
            }
        }

        return $this;
    }
}
