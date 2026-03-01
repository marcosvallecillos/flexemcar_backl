<?php

namespace App\Entity;

use App\Repository\ReservasAnuladasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservasAnuladasRepository::class)]
class ReservasAnuladas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dia = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $hora = null;

    #[ORM\ManyToOne(inversedBy: 'reservasAnuladas')]
    private ?User $user_id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Vehicles $vehicle_id = null;

    #[ORM\Column]
    private ?\DateTime $fecha_anulada = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDia(): ?\DateTime
    {
        return $this->dia;
    }

    public function setDia(\DateTime $dia): static
    {
        $this->dia = $dia;

        return $this;
    }

    public function getHora(): ?\DateTime
    {
        return $this->hora;
    }

    public function setHora(\DateTime $hora): static
    {
        $this->hora = $hora;

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

    public function getVehicleId(): ?Vehicles
    {
        return $this->vehicle_id;
    }

    public function setVehicleId(?Vehicles $vehicle_id): static
    {
        $this->vehicle_id = $vehicle_id;

        return $this;
    }

    public function getFechaAnulada(): ?\DateTime
    {
        return $this->fecha_anulada;
    }

    public function setFechaAnulada(\DateTime $fecha_anulada): static
    {
        $this->fecha_anulada = $fecha_anulada;

        return $this;
    }
}
