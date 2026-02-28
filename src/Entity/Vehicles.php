<?php

namespace App\Entity;

use App\Repository\VehiclesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiclesRepository::class)]
class Vehicles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(length: 255, nullable: true)] //t
    private ?string $marca = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)] //t
    private ?string $motor = null;

    
    #[ORM\Column(nullable: true)]
    private ?int $km = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    

    /**
     * @var Collection<int, Favorites>
     */
    #[ORM\ManyToMany(targetEntity: Favorites::class, mappedBy: 'vehicle_id')]
    private Collection $favorite_id;

    /**
     * @var Collection<int, VehiclesImages>
     */
    #[ORM\OneToMany(targetEntity: VehiclesImages::class, mappedBy: 'vehicle_id')]
    private Collection $vehicles_images_id;

    #[ORM\ManyToOne(inversedBy: 'vehicles_id')]
    private ?Reservas $reserva_id = null;
    public function __construct()
    {
        $this->favorite_id = new ArrayCollection();
        $this->vehicles_images_id = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function setMarca(?string $marca): static
    {
        $this->marca = $marca;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getMotor(): ?string
    {
        return $this->motor;
    }

    public function setMotor(?string $motor): static
    {
        $this->motor = $motor;

        return $this;
    }

    public function getKm(): ?int
    {
        return $this->km;
    }

    public function setKm(?int $km): static
    {
        $this->km = $km;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    
    /**
     * @return Collection<int, Favorites>
     */
    public function getFavoriteId(): Collection
    {
        return $this->favorite_id;
    }

    public function addFavoriteId(Favorites $favoriteId): static
    {
        if (!$this->favorite_id->contains($favoriteId)) {
            $this->favorite_id->add($favoriteId);
            $favoriteId->addVehicleId($this);
        }

        return $this;
    }

    public function removeFavoriteId(Favorites $favoriteId): static
    {
        if ($this->favorite_id->removeElement($favoriteId)) {
            $favoriteId->removeVehicleId($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, VehiclesImages>
     */
    public function getVehiclesImagesId(): Collection
    {
        return $this->vehicles_images_id;
    }

    public function addVehiclesImagesId(VehiclesImages $vehiclesImagesId): static
    {
        if (!$this->vehicles_images_id->contains($vehiclesImagesId)) {
            $this->vehicles_images_id->add($vehiclesImagesId);
            $vehiclesImagesId->setVehicleId($this);
        }

        return $this;
    }

    public function removeVehiclesImagesId(VehiclesImages $vehiclesImagesId): static
    {
        if ($this->vehicles_images_id->removeElement($vehiclesImagesId)) {
            // set the owning side to null (unless already changed)
            if ($vehiclesImagesId->getVehicleId() === $this) {
                $vehiclesImagesId->setVehicleId(null);
            }
        }

        return $this;
    }

    public function getReservas(): ?Reservas
    {
        return $this->reserva_id;
    }

    public function setReservas(?Reservas $reserva_id): static
    {
        $this->reserva_id = $reserva_id;

        return $this;
    }
}
