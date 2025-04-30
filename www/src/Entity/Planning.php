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
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'profile.id' => 'exact',
        'vibe.id' => 'exact',
        'createdAt' => 'partial',
    ]
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
    #[Groups(['planning:read', 'planning:write'])]
    private Collection $rooms;

    #[ORM\ManyToOne(inversedBy: 'plannings')]
    #[Groups(['planning:read', 'planning:write'])]
    private ?Profile $profile = null;

    #[ORM\Column(length: 5)]
    #[Groups(['planning:read', 'planning:write'])]
    private ?string $hourStart = null;

    #[ORM\Column(length: 5)]
    #[Groups(['planning:read', 'planning:write'])]
    private ?string $hourEnd = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['planning:read', 'planning:write'])]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(length: 10)]
    #[Groups(['planning:read', 'planning:write'])]
    private ?string $dayCreation = null;

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

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getHourStart(): ?string
    {
        return $this->hourStart;
    }

    public function setHourStart(string $hourStart): static
    {
        $this->hourStart = $hourStart;

        return $this;
    }

    public function getHourEnd(): ?string
    {
        return $this->hourEnd;
    }

    public function setHourEnd(string $hourEnd): static
    {
        $this->hourEnd = $hourEnd;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setCreatedAt(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDayCreation(): ?string
    {
        return $this->dayCreation;
    }

    public function setDayCreation(string $dayCreation): static
    {
        $this->dayCreation = $dayCreation;

        return $this;
    }
}
