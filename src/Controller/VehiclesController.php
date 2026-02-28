<?php

namespace App\Controller;
use App\Entity\Reservas;
use App\Entity\Vehicles;
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
                    'fecha' => $reserva->getDate(),
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
                'fecha' => $reserva->getDate(),
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
    
}