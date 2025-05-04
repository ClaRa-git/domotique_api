<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch()
    ],
    normalizationContext: ['groups' => ['profile:read']],
    denormalizationContext: ['groups' => ['profile:write']],
)]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['profile:read', 'playlist:read', 'vibe:read', 'vibe_playing:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['profile:read', 'profile:write', 'playlist:read', 'vibe:read', 'vibe_playing:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(['profile:write'])]
    private ?string $password = null;

    /**
     * @var Collection<int, Playlist>
     */
    #[ORM\OneToMany(targetEntity: Playlist::class, mappedBy: 'profile')]
    #[Groups(['profile:read'])]
    private Collection $playlists;

    /**
     * @var Collection<int, Vibe>
     */
    #[ORM\OneToMany(targetEntity: Vibe::class, mappedBy: 'profile')]
    #[Groups(['profile:read', 'profile:write'])]
    private Collection $vibes;

    #[ORM\ManyToOne(inversedBy: 'profiles')]
    #[Groups(['profile:read', 'profile:write'])]
    private ?Avatar $avatar = null;

    /**
     * @var Collection<int, Planning>
     */
    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'profile')]
    private Collection $plannings;

    /**
     * @var Collection<int, VibePlaying>
     */
    #[ORM\OneToMany(targetEntity: VibePlaying::class, mappedBy: 'profile')]
    private Collection $vibePlayings;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
        $this->vibes = new ArrayCollection();
        $this->plannings = new ArrayCollection();
        $this->vibePlayings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, Playlist>
     */
    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): static
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists->add($playlist);
            $playlist->setProfile($this);
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): static
    {
        if ($this->playlists->removeElement($playlist)) {
            // set the owning side to null (unless already changed)
            if ($playlist->getProfile() === $this) {
                $playlist->setProfile(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vibe>
     */
    public function getVibes(): Collection
    {
        return $this->vibes;
    }

    public function addVibe(Vibe $vibe): static
    {
        if (!$this->vibes->contains($vibe)) {
            $this->vibes->add($vibe);
            $vibe->setProfile($this);
        }

        return $this;
    }

    public function removeVibe(Vibe $vibe): static
    {
        if ($this->vibes->removeElement($vibe)) {
            // set the owning side to null (unless already changed)
            if ($vibe->getProfile() === $this) {
                $vibe->setProfile(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function setAvatar(?Avatar $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function __toString(): string
    {
        return $this->username;
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
            $planning->setProfile($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getProfile() === $this) {
                $planning->setProfile(null);
            }
        }

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
            $vibePlaying->setProfile($this);
        }

        return $this;
    }

    public function removeVibePlaying(VibePlaying $vibePlaying): static
    {
        if ($this->vibePlayings->removeElement($vibePlaying)) {
            // set the owning side to null (unless already changed)
            if ($vibePlaying->getProfile() === $this) {
                $vibePlaying->setProfile(null);
            }
        }

        return $this;
    }
}
