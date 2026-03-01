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
    public function index(VehiclesRepository $vehiclesRepository): JsonResponse
    {
        $vehicles = $vehiclesRepository->findAll();
        if (!$vehicles) {
            return new JsonResponse(['message' => 'No se encontraron vehículos'], Response::HTTP_NOT_FOUND);
        }
        $data = [];
        
        foreach ($vehicles as $vehicle) {
            $reserva = $vehicle->getReservas(); // Puede ser null o una única reserva

            $reservasData = [];
            if ($reserva !== null) {
                $reservasData[] = [
                    'id' => $reserva->getId(),
                    'dia' => $reserva->getDia() ? $reserva->getDia()->format('Y-m-d') : null,
                    'hora' => $reserva->getHora() ? $reserva->getHora()->format('H:i:s') : null,
                    'estado' => $reserva->getStatus(),
                ];
            }

            $data[] = [
                'id' => $vehicle->getId(),
                'marca' => $vehicle->getMarca(),
                'modelo' => $vehicle->getModel(),
                'año' => $vehicle->getYear(),
                'motor' => $vehicle->getMotor(),
                'year' => $vehicle->getYear(),
                'km' => $vehicle->getKm(),
                'image_url' => $vehicle->getVehiclesImagesId()->map(fn($image) => $image->getImageUrl())->toArray() ?? null,
                'precio' => $vehicle->getPrice(),
                'reservas' => $reservasData,
            ];
        }
        
        return new JsonResponse($data);
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
        $reserva = $vehicle->getReservas(); 

        $reservasData = [];
        if ($reserva !== null) {
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
            'image_url' => $vehicle->getVehiclesImagesId()->map(fn($image) => $image->getImageUrl())->toArray() ?? null,   
            'precio' => $vehicle->getPrice(),
            'reservas' => $reservasData,
        ]);
    }
#[Route('/favoritos/{id}', name: 'agregar_a_favoritos', methods: ['GET', 'POST'])]
    public function agregarAFavoritos(int $id, Request $request, EntityManagerInterface $em): JsonResponse
        {
        $vehicle = $em->getRepository(Vehicles::class)->find($id);
        if (!$vehicle) {
            return new JsonResponse(['error' => 'Vehículo no encontrado'], 404);
        }

        if ($request->isMethod('GET')) {
            return new JsonResponse([
                'status' => 'success',
                'vehicle' => [
                    'id' => $vehicle->getId(),
                    'modelo' => $vehicle->getModel(),
                    'marca' => $vehicle->getMarca(),
                    'precio' => $vehicle->getPrice(),
                    'motor' => $vehicle->getMotor(),
                    'km' => $vehicle->getKm(),
                    'year' => $vehicle->getYear(),
                    'image_url' => $vehicle->getVehiclesImagesId()->map(fn($image) => $image->getImageUrl())->toArray() ?? null,
                    
                    'favorite' => $vehicle->isFavorite(),

                ]
            ]);
        }

        $data = json_decode($request->getContent(), true);
        $usuario = isset($data['usuario_id']) ? $em->getRepository(User::class)->find($data['usuario_id']) : null;

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado o ID faltante'], 400);
        }

        $favoritoRepo = $em->getRepository(Favorites::class);
        // Buscar si ya existe un favorito para este usuario y vehículo usando QueryBuilder
        $favorito = $favoritoRepo->createQueryBuilder('f')
            ->where('f.user_id = :usuario')
            ->andWhere('f.vehicle_id = :vehicle')
            ->setParameter('usuario', $usuario)
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$favorito) {
            $favorito = new Favorites();
            $favorito->setUserId($usuario);
            $favorito->setVehicleId($vehicle);
            $favorito->setIsFavorite(true);
            $favorito->setCreatedAt(new \DateTime());
        } else {
            // Si existe, alternar el estado
            $favorito->setIsFavorite(!$favorito->isFavorite());
        }

        try {
            $em->persist($favorito);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => $favorito->isFavorite() ? 'vehicle agregado a favoritos' : 'vehicle removido de favoritos',
                'vehicle' => [
                    'id' => $vehicle->getId(),
                    'marca' => $vehicle->getMarca(),
                    'modelo' => $vehicle->getModel(),
                    'precio' => $vehicle->getPrice(),
                    'motor' => $vehicle->getMotor(),
                    'km' => $vehicle->getKm(),
                    'year' => $vehicle->getYear(),
                    'image_url' => $vehicle->getVehiclesImagesId()->map(fn($image) => $image->getImageUrl())->toArray() ?? null,    
                    'favorite' => $favorito->isFavorite()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al actualizar el estado de favorito',
                'error' => $e->getMessage()
            ], 500);
        }
}
    #[Route('/favoritos/usuario/{id}', name: 'get_favoritos_usuario', methods: ['GET'])]
    public function getFavoritosUsuario(int $id, EntityManagerInterface $em): JsonResponse
    {
        $usuario = $em->getRepository(User::class)->find($id);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        // Buscar todos los favoritos del usuario
        $favoritos = $em->getRepository(Favorites::class)->createQueryBuilder('f')
            ->where('f.user_id = :usuario')
            ->andWhere('f.isFavorite = :isFavorite')
            ->setParameter('usuario', $usuario)
            ->setParameter('isFavorite', true)
            ->getQuery()
            ->getResult();

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
            'user_id' => $usuario->getId(),
            'favoritos' => $data
        ]);
    }
}