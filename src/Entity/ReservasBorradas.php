<?php

namespace App\Entity;
use App\Repository\ReservasBorradasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservasBorradasRepository::class)]
class ReservasBorradas
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

    #[ORM\ManyToOne(inversedBy: 'reservasBorradas')]
    private ?User $user_id = null;

    #[ORM\Column]
    private ?\DateTime $borradaEn = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Vehicles $vehicle_id = null;

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

      public static function fromReserva(\App\Entity\Reservas $reserva): self
    {
        $rb = new self();
        $rb->setStatus($reserva->getStatus());
        $rb->setVehicleId($reserva->getVehicleId());
        $rb->setDia($reserva->getDia());
        $rb->setHora($reserva->getHora());
        $rb->setUserId($reserva->getUserId());
        $rb->setBorradaEn(new \DateTime());

        return $rb;
    }

      public function getBorradaEn(): ?\DateTime
      {
          return $this->borradaEn;
      }

      public function setBorradaEn(\DateTime $borradaEn): static
      {
          $this->borradaEn = $borradaEn;

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
}
