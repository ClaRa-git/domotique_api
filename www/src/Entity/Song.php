<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\SongRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: SongRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    normalizationContext: ['groups' => ['song:read']]
)]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['song:read', 'playlist:read', 'profile:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['song:read', 'playlist:read', 'profile:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    #[Groups(['song:read', 'playlist:read'])]
    private ?string $artist = null;

    #[ORM\Column]
    #[Groups(['song:read', 'playlist:read'])]
    private ?int $duration = null;

    #[ORM\Column(length: 255)]
    #[Groups(['song:read', 'playlist:read'])]
    private ?string $filePath = null;

    #[Vich\UploadableField(mapping: 'songs', fileNameProperty: 'filePath')]
    private ?File $filePathFile = null;

    /**
     * @var Collection<int, Playlist>
     */
    #[ORM\ManyToMany(targetEntity: Playlist::class, inversedBy: 'songs')]
    #[Groups(['song:read'])]
    private Collection $playlists;

    #[ORM\Column(length: 255)]
    #[Groups(['song:read', 'playlist:read'])]
    private ?string $imagePath = null;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
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

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    
    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFilePathFile(): ?File
    {
        return $this->filePathFile;
    }

    public function setFilePathFile(?File $filePathFile): static
    {
        $this->filePathFile = $filePathFile;

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
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): static
    {
        $this->playlists->removeElement($playlist);

        return $this;
    }

    public function __toString(): string
    {
        return $this->title;
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
