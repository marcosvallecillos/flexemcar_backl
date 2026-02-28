<?php

namespace App\Entity;

use App\Repository\VehiclesImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiclesImagesRepository::class)]
class VehiclesImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles_images_id')]
    private ?Vehicles $vehicle_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleId(): ?Vehicles
    {
        return $this->vehicle_id;
    }

    public function setVehicleId(?Vehicles $vehicle_id): static
    {
        $this->vehicle_id = $vehicle_id;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }
}
