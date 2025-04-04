<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\PlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(),
        new Post(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['planning:read']],
    denormalizationContext: ['groups' => ['planning:write']],
)]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['planning:read', 'profile:read', 'room:read', 'vibe:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['planning:read', 'planning:write', 'profile:read', 'room:read', 'vibe:read'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['planning:read', 'planning:write', 'profile:read', 'room:read'])]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['planning:read', 'planning:write', 'profile:read', 'room:read'])]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column(length: 50)]
    #[Groups(['planning:read', 'planning:write', 'profile:read', 'room:read'])]
    private ?string $recurrence = null;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    #[Groups(['planning:read', 'planning:write'])]
    private ?Vibe $vibe = null;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\ManyToMany(targetEntity: Room::class, mappedBy: 'plannings')]
    #[Groups(['planning:read', 'planning:write', 'profile:read'])]
    private Collection $rooms;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
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

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getRecurrence(): ?string
    {
        return $this->recurrence;
    }

    public function setRecurrence(string $recurrence): static
    {
        $this->recurrence = $recurrence;

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

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->addPlanning($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            $room->removePlanning($this);
        }

        return $this;
    }
}
