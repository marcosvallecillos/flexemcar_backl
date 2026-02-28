<?php

namespace App\Entity;

use App\Repository\ReservasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservasRepository::class)]
class Reservas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'reserva_id')]
    private ?User $user_id = null;

    /**
     * @var Collection<int, Vehicles>
     */
    #[ORM\OneToMany(targetEntity: Vehicles::class, mappedBy: 'reserva_id')]
    private Collection $vehicles_id;

    public function __construct()
    {
        $this->vehicles_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

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

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, Vehicles>
     */
    public function getVehiclesId(): Collection
    {
        return $this->vehicles_id;
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
}
