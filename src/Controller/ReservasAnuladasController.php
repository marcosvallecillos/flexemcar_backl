<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ReservasAnuladasRepository;

use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;


#[Route('api/reservasAnuladas')]
final class ReservasAnuladasController extends AbstractController
{
     private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route(name: 'app_reservas_anuladas_index', methods: ['GET'])]
    public function index(ReservasAnuladasRepository $reservasAnuladasRepository): JsonResponse
    {
        $reservas = $reservasAnuladasRepository->findBy(['status' => 'anulada']);
        if (!$reservas) {
            return new JsonResponse(['message' => 'No se encontraron reservas anuladas'], Response::HTTP_NOT_FOUND);
        }
         $data = [];
         foreach ($reservas as $reserva) {
                $vehiculoId = null;
                $valoracion = null;
                $comentario = null;
                $servicioRating = null;
                $fecha = null;

            $data[] = [
                'usuario_id' => $reserva->getUserId() ? $reserva->getUserId()->getId() : null,
                'vehiculo_id' => $reserva->getVehicleId() ? $reserva->getVehicleId()->getId() : null,
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'valoracion' => $valoracion,
                'valoracion_comentario' => $comentario,
                'valoracion_servicio' => $servicioRating,
                'valoracion_fecha' => $fecha
            ];

        }
        
        return new JsonResponse($data);
    }

}