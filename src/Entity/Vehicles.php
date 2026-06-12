<?php

namespace App\Entity;

use App\Repository\VehiclesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiclesRepository::class)]
#[ORM\Table(name: 'vehicle_models')]
class Vehicles
{
    #[ORM\Id]
    #[ORM\Column(length: 20)]
    private ?string $id = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $price = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $price_str = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $length = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $width = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $weight = null;

    #[ORM\Column(nullable: true)]
    private ?int $beds = null;

    #[ORM\Column(nullable: true)]
    private ?int $seats = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $features = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $brochure_url = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $kilometers = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $engine = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_favorite = null;



    /**
     * @var Collection<int, Favorites>
     */
    #[ORM\OneToMany(targetEntity: Favorites::class, mappedBy: 'vehicle_id')]
    private Collection $favorite_id;

    /**
     * @var Collection<int, VehiclesImages>
     */
    #[ORM\OneToMany(targetEntity: VehiclesImages::class, mappedBy: 'vehicle_id')]
    private Collection $vehicles_images_id;

    /**
     * @var Collection<int, Reservas>
     */
    #[ORM\OneToMany(targetEntity: Reservas::class, mappedBy: 'vehicle')]
    private Collection $reservas;

    public function __construct()
    {
        $this->reservas = new ArrayCollection();
        $this->favorite_id = new ArrayCollection();
        $this->vehicles_images_id = new ArrayCollection();
    }

    public function getId(): ?string { return $this->id; }
    public function setId(string $id): self { $this->id = $id; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }

    public function getModel(): ?string { return $this->name; } // Alias para compatibilidad

    public function getBrand(): ?string { return $this->brand; }
    public function setBrand(?string $brand): self { $this->brand = $brand; return $this; }

    public function getPrice(): ?float { return $this->price; }
    public function setPrice(?float $price): self { $this->price = $price; return $this; }

    public function getImageUrl(): ?string { return $this->image_url; }
    public function setImageUrl(?string $image_url): self { $this->image_url = $image_url; return $this; }
    
    public function getMainImage(): ?string { return $this->image_url; }

    /**
     * @return Collection<int, VehiclesImages>
     */
    public function getVehiclesImagesId(): Collection { return $this->vehicles_images_id; }

    /**
     * @return Collection<int, Reservas>
     */
    public function getReservas(): Collection { return $this->reservas; }

    public function addReserva(Reservas $reserva): self
    {
        if (!$this->reservas->contains($reserva)) {
            $this->reservas->add($reserva);
            $reserva->setVehicle($this);
        }
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPriceStr(): ?string
    {
        return $this->price_str;
    }

    public function setPriceStr(?string $price_str): static
    {
        $this->price_str = $price_str;

        return $this;
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function setLength(?string $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getBeds(): ?int
    {
        return $this->beds;
    }

    public function setBeds(?int $beds): static
    {
        $this->beds = $beds;

        return $this;
    }

    

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(?int $seats): static
    {
        $this->seats = $seats;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): static
    {
        $this->features = $features;

        return $this;
    }

    public function getBrochureUrl(): ?string
    {
        return $this->brochure_url;
    }

    public function setBrochureUrl(?string $brochure_url): static
    {
        $this->brochure_url = $brochure_url;

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
            $favoriteId->setVehicleId($this);
        }

        return $this;
    }

    public function removeFavoriteId(Favorites $favoriteId): static
    {
        if ($this->favorite_id->removeElement($favoriteId)) {
            // set the owning side to null (unless already changed)
            if ($favoriteId->getVehicleId() === $this) {
                $favoriteId->setVehicleId(null);
            }
        }

        return $this;
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

    public function removeReserva(Reservas $reserva): static
    {
        if ($this->reservas->removeElement($reserva)) {
            // set the owning side to null (unless already changed)
            if ($reserva->getVehicle() === $this) {
                $reserva->setVehicle(null);
            }
        }

        return $this;
    }

    public function isFavorite(): ?bool
    {
        return $this->is_favorite;
    }

    public function setIsFavorite(?bool $is_favorite): static
    {
        $this->is_favorite = $is_favorite;

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

    public function getKilometers(): ?string
    {
        return $this->kilometers;
    }

    public function setKilometers(?string $kilometers): static
    {
        $this->kilometers = $kilometers;

        return $this;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function setEngine(?string $engine): static
    {
        $this->engine = $engine;

        return $this;
    }
}
