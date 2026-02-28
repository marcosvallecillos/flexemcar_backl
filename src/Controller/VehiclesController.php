<?php

namespace App\Controller;
use App\Entity\Reservas;
use App\Entity\Vehicles;
use Symfony\Component\Mailer\MailerInterface;
use App\Form\UsuariosType;
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
                'precio' => $vehicle->getPrice(),
                'reservas' => $reservasData,
            ];
        }
        
        return new JsonResponse($data);
    }
}
