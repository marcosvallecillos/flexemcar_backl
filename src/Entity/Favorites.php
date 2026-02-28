<?php

namespace App\Entity;

use App\Repository\FavoritesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoritesRepository::class)]
class Favorites
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favorite_id')]
    private ?User $user_id = null;

    /**
     * @var Collection<int, Vehicles>
     */
    #[ORM\ManyToMany(targetEntity: Vehicles::class, inversedBy: 'favorite_id')]
    private Collection $vehicle_id;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $created_at = null;

    public function __construct()
    {
        $this->vehicle_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
    public function getVehicleId(): Collection
    {
        return $this->vehicle_id;
    }

    public function addVehicleId(Vehicles $vehicleId): static
    {
        if (!$this->vehicle_id->contains($vehicleId)) {
            $this->vehicle_id->add($vehicleId);
        }

        return $this;
    }

    public function removeVehicleId(Vehicles $vehicleId): static
    {
        $this->vehicle_id->removeElement($vehicleId);

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
}
