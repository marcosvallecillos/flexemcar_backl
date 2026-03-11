<?php

namespace App\Controller;
use App\Entity\Reservas;
use App\Entity\Vehicles;
use App\Entity\User;
use App\Entity\Favorites;
use Symfony\Component\Mailer\MailerInterface;
use App\Form\vehiclesType;
use App\Repository\VehiclesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[Route('/api/vehicles')]
final class VehiclesController extends AbstractController
{

#[Route(name: 'app_vehicles_index', methods: ['GET'])]
    public function index(
    VehiclesRepository $vehiclesRepository,
    EntityManagerInterface $em,
    Request $request
): JsonResponse {
    $vehicles = $vehiclesRepository->findAll();
    $userId = $request->query->get('usuario_id');
    
    $favoritosIds = [];
    if ($userId) {
        $usuario = $em->getRepository(User::class)->find($userId);
        if ($usuario) {
            $favoritos = $em->getRepository(Favorites::class)->findBy([
                'user_id'    => $usuario,
                'isFavorite' => true
            ]);
            $favoritosIds = array_map(
                fn($f) => $f->getVehicleId()->getId(),
                $favoritos
            );
        }
    }

    $data = [];
    foreach ($vehicles as $vehicle) {
        $data[] = [
            'id'          => $vehicle->getId(),
            'marca'       => $vehicle->getMarca(),
            'modelo'      => $vehicle->getModel(),
            'year'        => $vehicle->getYear(),
            'motor'       => $vehicle->getMotor(),
            'km'          => $vehicle->getKm(),
            'precio'      => $vehicle->getPrice(),
            'description' => $vehicle->getDescription(),
            'is_favorite' => in_array($vehicle->getId(), $favoritosIds), // ← por usuario
            'image_url'   => $vehicle->getVehiclesImagesId()
                ->map(fn($img) => 'http://localhost:8000' . $img->getImageUrl())
                ->toArray(),
            'reservas'    => $vehicle->getReservas()->map(fn($r) => [
                'id'     => $r->getId(),
                'dia'    => $r->getDia()?->format('Y-m-d'),
                'hora'   => $r->getHora()?->format('H:i:s'),
                'estado' => $r->getStatus(),
            ])->toArray(),
        ];
    }

    return $this->json($data);
}
#[Route('/new', name: 'app_vehicles_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    
    {
        $data = json_decode($request->getContent(), true);
    
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
    
        $vehicle = new Vehicles();
        $vehicle->setMarca($data['marca'] ?? null);
        $vehicle->setModel($data['modelo'] ?? null);
        $vehicle->setYear($data['year'] ?? null);
        $vehicle->setMotor($data['motor'] ?? null);
        $vehicle->setPrice($data['precio'] ?? null);
         $vehicle->setDescription($data['descripcion'] ?? null);
        $vehicle->setIsFavorite($data['is_favorite'] ?? null);
        $vehicle->setKm($data['km'] ?? null);
        $entityManager->persist($vehicle);
        $entityManager->flush();
      
        return new JsonResponse([
                'status' => 'vehicle creado',
                ], 200);
    }
#[Route('/{id}', name: 'app_vehicles_show', methods: ['GET'])]
    public function show(Vehicles $vehicle): JsonResponse
    {
        $reservas = $vehicle->getReservas(); 

        $reservasData = [];
        foreach ($reservas as $reserva) {
            $reservasData[] = [
                'id' => $reserva->getId(),
                'dia' => $reserva->getDia() ? $reserva->getDia()->format('Y-m-d') : null,
                'hora' => $reserva->getHora() ? $reserva->getHora()->format('H:i:s') : null,
                'estado' => $reserva->getStatus(),
            ];
        }

        return new JsonResponse([
            'id' => $vehicle->getId(),
            'marca' => $vehicle->getMarca(),
            'modelo' => $vehicle->getModel(),
            'año' => $vehicle->getYear(),
            'motor' => $vehicle->getMotor(),
            'year' => $vehicle->getYear(),
            'km' => $vehicle->getKm(),
            'descripcion '=> $vehicle -> getDescription(),
            'is_favorite'=> $vehicle -> isFavorite(),
            'image_url' => $vehicle->getVehiclesImagesId()->map(fn($image) => $image->getImageUrl())->toArray() ?? null,   
            'precio' => $vehicle->getPrice(),
            'reservas' => $reservasData,
        ]);
    }#[Route('/favoritos/{id}', name: 'agregar_a_favoritos', methods: ['GET', 'POST'])]
public function agregarAFavoritos(
    int $id,
    Request $request,
    EntityManagerInterface $em
): JsonResponse {
    $vehicle = $em->getRepository(Vehicles::class)->find($id);
    if (!$vehicle) {
        return new JsonResponse(['error' => 'Vehículo no encontrado'], 404);
    }

    if ($request->isMethod('GET')) {
        return new JsonResponse([
            'status'  => 'success',
            'vehicle' => [
                'id'          => $vehicle->getId(),
                'model'       => $vehicle->getModel(),
                'is_favorite' => $vehicle->isFavorite(),
            ]
        ]);
    }

    // ── POST ─────────────────────────────────────────────────
    $data    = json_decode($request->getContent(), true);
    $usuario = isset($data['usuario_id'])
        ? $em->getRepository(User::class)->find($data['usuario_id'])
        : null;

    if (!$usuario) {
        return new JsonResponse(['error' => 'Usuario no encontrado o ID faltante'], 400);
    }

    // 1. Toggle en tabla Favorites
    $favorito = $em->getRepository(Favorites::class)->findOneBy([
        'user_id'    => $usuario,
        'vehicle_id' => $vehicle
    ]);

    if (!$favorito) {
        $favorito = new Favorites();
        $favorito->setUserId($usuario);
        $favorito->setVehicleId($vehicle);
        $favorito->setIsFavorite(true);
        $favorito->setCreatedAt(new \DateTime());
    } else {
        $favorito->setIsFavorite(!$favorito->isFavorite());
    }

    // 2. Sincronizar is_favorite en Vehicles
    $vehicle->setIsFavorite($favorito->isFavorite());

    try {
        $em->persist($favorito);
        $em->flush();

        return new JsonResponse([
            'status'      => 'success',
            'message'     => $favorito->isFavorite()
                ? 'Vehículo agregado a favoritos'
                : 'Vehículo removido de favoritos',
            'isFavorite'  => $favorito->isFavorite(),
            'vehicle'     => [
                'id'          => $vehicle->getId(),
                'model'       => $vehicle->getModel(),
                'is_favorite' => $vehicle->isFavorite(),
            ]
        ]);
    } catch (\Exception $e) {
        return new JsonResponse([
            'status'  => 'error',
            'message' => 'Error al actualizar favorito',
            'error'   => $e->getMessage()
        ], 500);
    }
}
    #[Route('/favoritos/usuario/{id}', name: 'get_favoritos_usuario', methods: ['GET'])]
    public function getFavoritosUsuario(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user_id = $em->getRepository(User::class)->find($id);

        if (!$user_id) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }
        $favoritos = $em->getRepository(Favorites::class)->findBy([
            'user_id' => $user_id,
            'isFavorite' => true
        ]);
        
        $data = [];
        foreach ($favoritos as $favorito) {
            $vehicle = $favorito->getVehicleId();
            $data[] = [
                'id' => $vehicle->getId(),
                'marca' => $vehicle->getMarca(),
                'modelo' => $vehicle->getModel(),
                'precio' => $vehicle->getPrice(),
                'motor' => $vehicle->getMotor(),
                'km' => $vehicle->getKm(),
                'year' => $vehicle->getYear(),
                'image_url' => $vehicle->getVehiclesImagesId()->map(fn($image) => $image->getImageUrl())->toArray() ?? null,    
                'favorite' => $favorito->isFavorite()
            ];
        }

        return new JsonResponse([
            'status' => 'success',
            'user_id' => $user_id->getId(),
            'favoritos' => $data
        ]);
    }
}