<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\PlaylistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PlaylistRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(),
        new Post(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['playlist:read']],
    denormalizationContext: ['groups' => ['playlist:write']],
)]
class Playlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['playlist:read', 'profile:read', 'song:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['playlist:read', 'playlist:write', 'profile:read', 'song:read', 'vibe:read'])]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'playlists')]
    #[Groups(['playlist:read', 'playlist:write'])]
    private ?Profile $profile = null;

    /**
     * @var Collection<int, Vibe>
     */
    #[ORM\OneToMany(targetEntity: Vibe::class, mappedBy: 'playlist')]
    #[Groups(['playlist:read', 'playlist:write'])]
    private Collection $vibes;

    /**
     * @var Collection<int, Song>
     */
    #[ORM\ManyToMany(targetEntity: Song::class, mappedBy: 'playlists')]
    #[Groups(['playlist:read', 'playlist:write', 'profile:read'])]
    private Collection $songs;

    #[ORM\Column(length: 255)]
    #[Groups(['playlist:read', 'playlist:write', 'profile:read'])]
    private ?string $imagePath = null;

    public function __construct()
    {
        $this->vibes = new ArrayCollection();
        $this->songs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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
            $vibe->setPlaylist($this);
        }

        return $this;
    }

    public function removeVibe(Vibe $vibe): static
    {
        if ($this->vibes->removeElement($vibe)) {
            // set the owning side to null (unless already changed)
            if ($vibe->getPlaylist() === $this) {
                $vibe->setPlaylist(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): static
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->addPlaylist($this);
        }

        return $this;
    }

    public function removeSong(Song $song): static
    {
        if ($this->songs->removeElement($song)) {
            $song->removePlaylist($this);
        }

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
}
