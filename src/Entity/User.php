<?php

namespace App\Entity;

use App\Repository\UserRepository;
use BcMath\Number;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?int $telefono = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rol = null;

    /**
     * @var Collection<int, Reservas>
     */
    #[ORM\OneToMany(targetEntity: Reservas::class, mappedBy: 'user', cascade: ['remove'], orphanRemoval: true)]
private Collection $reservas;

    

    public function getReservas(): Collection
    {
        return $this->reservas;
    }

    public function addReserva(Reservas $reserva): static
    {
        if (!$this->reservas->contains($reserva)) {
            $this->reservas->add($reserva);
            $reserva->setUser($this);
        }
        return $this;
    }

    public function removeReserva(Reservas $reserva): static
    {
        if ($this->reservas->removeElement($reserva)) {
            if ($reserva->getUser() === $this) {
                $reserva->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @var Collection<int, Favorites>
     */
    #[ORM\OneToMany(targetEntity: Favorites::class, mappedBy: 'user_id')]
    private Collection $favorite_id;

    /**
     * @var Collection<int, Reviews>
     */
    #[ORM\OneToMany(targetEntity: Reviews::class, mappedBy: 'usuario_id')]
    private Collection $review;

    /**
     * @var Collection<int, ReservasBorradas>
     */
    #[ORM\OneToMany(targetEntity: ReservasBorradas::class, mappedBy: 'user_id')]
    private Collection $reservasBorradas;

    /**
     * @var Collection<int, ReservasAnuladas>
     */
    #[ORM\OneToMany(targetEntity: ReservasAnuladas::class, mappedBy: 'user_id')]
    private Collection $reservasAnuladas;

    public function __construct()
    {
        $this->reservas = new ArrayCollection();
        $this->favorite_id = new ArrayCollection();
        $this->review = new ArrayCollection();
        $this->reservasBorradas = new ArrayCollection();
        $this->reservasAnuladas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

     public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // 2️⃣ Roles del usuario
    public function getRoles(): array
    {
        // Siempre devuelve un array, incluso si solo hay un rol
        return [$this->rol ?? 'usuario'];
    }

    // 3️⃣ Contraseña
    public function getPassword(): string
    {
        return $this->password;
    }

    // 4️⃣ Limpiar datos sensibles
    public function eraseCredentials(): void
    {
        // Aquí puedes limpiar campos temporales como $plainPassword si tuvieras
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getTelefono(): ?int 
    {
        return $this->telefono;
    }

    public function setTelefono(?int  $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(?string $rol): static
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * @return Collection<int, Reservas>
     */
  
    

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
            $favoriteId->setUserId($this);
        }

        return $this;
    }

    public function removeFavoriteId(Favorites $favoriteId): static
    {
        if ($this->favorite_id->removeElement($favoriteId)) {
            // set the owning side to null (unless already changed)
            if ($favoriteId->getUserId() === $this) {
                $favoriteId->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reviews>
     */
    public function getReview(): Collection
    {
        return $this->review;
    }

    public function addReview(Reviews $review): static
    {
        if (!$this->review->contains($review)) {
            $this->review->add($review);
            $review->setUsuarioId($this);
        }

        return $this;
    }

    public function removeReview(Reviews $review): static
    {
        if ($this->review->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUsuarioId() === $this) {
                $review->setUsuarioId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservasBorradas>
     */
    public function getReservasBorradas(): Collection
    {
        return $this->reservasBorradas;
    }

    public function addReservasBorrada(ReservasBorradas $reservasBorrada): static
    {
        if (!$this->reservasBorradas->contains($reservasBorrada)) {
            $this->reservasBorradas->add($reservasBorrada);
            $reservasBorrada->setUserId($this);
        }

        return $this;
    }

    public function removeReservasBorrada(ReservasBorradas $reservasBorrada): static
    {
        if ($this->reservasBorradas->removeElement($reservasBorrada)) {
            // set the owning side to null (unless already changed)
            if ($reservasBorrada->getUserId() === $this) {
                $reservasBorrada->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservasAnuladas>
     */
    public function getReservasAnuladas(): Collection
    {
        return $this->reservasAnuladas;
    }

    public function addReservasAnulada(ReservasAnuladas $reservasAnulada): static
    {
        if (!$this->reservasAnuladas->contains($reservasAnulada)) {
            $this->reservasAnuladas->add($reservasAnulada);
            $reservasAnulada->setUserId($this);
        }

        return $this;
    }

    public function removeReservasAnulada(ReservasAnuladas $reservasAnulada): static
    {
        if ($this->reservasAnuladas->removeElement($reservasAnulada)) {
            // set the owning side to null (unless already changed)
            if ($reservasAnulada->getUserId() === $this) {
                $reservasAnulada->setUserId(null);
            }
        }

        return $this;
    }
}
