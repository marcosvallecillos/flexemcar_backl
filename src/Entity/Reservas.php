<?php

namespace App\Entity;

use App\Repository\ReservasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ReservasRepository::class)]
class Reservas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dia = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $hora = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $created_at = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservas')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    /**
     * @var Collection<int, Vehicles>
     */
    #[ORM\OneToMany(targetEntity: Vehicles::class, mappedBy: 'reserva_id')]
    private Collection $vehicles_id;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Reviews $review = null;

    public function __construct()
    {
        $this->vehicles_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

     public function getDia(): ?\DateTimeInterface
    {
        return $this->dia;
    }

    public function setDia(\DateTimeInterface $dia): static
    {
        $this->dia = $dia;

        return $this;
    }

    public function getHora(): ?\DateTimeInterface
    {
        return $this->hora;
    }

    public function setHora(\DateTimeInterface $hora): static
    {
        $this->hora = $hora;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    // Método de compatibilidad (deprecated, usar getUser/setUser)
    public function getUserId(): ?User
    {
        return $this->user;
    }

    public function setUserId(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Vehicles>
     */
    public function getVehiclesId(): Collection
    {
        return $this->vehicles_id;
    }
    public function setVehiclesId(Vehicles $vehiclesId): static
    {
        $this->vehicles_id = new ArrayCollection();
        $this->vehicles_id->add($vehiclesId);
        return $this;
    }

    public function addVehiclesId(Vehicles $vehiclesId): static
    {
        if (!$this->vehicles_id->contains($vehiclesId)) {
            $this->vehicles_id->add($vehiclesId);
            $vehiclesId->setReservaId($this);
        }

        return $this;
    }

    public function removeVehiclesId(Vehicles $vehiclesId): static
    {
        if ($this->vehicles_id->removeElement($vehiclesId)) {
            // set the owning side to null (unless already changed)
            if ($vehiclesId->getReservaId() === $this) {
                $vehiclesId->setReservaId(null);
            }
        }

        return $this;
    }

    public function getReview(): ?Reviews
    {
        return $this->review;
    }

    public function setReview(?Reviews $review): static
    {
        $this->review = $review;

        return $this;
    }
}
