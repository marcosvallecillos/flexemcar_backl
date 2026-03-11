<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Reviews;
use App\Entity\User;
use App\Entity\Reservas;
use App\Repository\ReviewsRepository;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/review')]
final class ReviewsController extends AbstractController
{
     private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route(name: 'app_reviews_index', methods: ['GET'])]
    public function index(ReviewsRepository $reviewsRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $reviews = $reviewsRepository->findAll();
        $data = [];
        
        foreach ($reviews as $review) {
            $data[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'created_at' => $review->getCreatedAt()->format('Y-m-d'),
                'usuario_id' => $review->getUsuarioId() ? $review->getUsuarioId()->getId() : null,
                'reserva_id' => $review->getReservaId() ? $review->getReservaId()->getId() : null
            ];
        }
        
        return new JsonResponse($data);
    }
        
    

    #[Route('/new', name: 'api_crear_review', methods: ['GET','POST'])]
    public function crear(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Unificar obtención de datos para JSON y form-data
        $data = $request->request->all();
        if (empty($data)) {
            $content = $request->getContent();
            if (!empty($content)) {
                $data = json_decode($content, true);
            }
        }

        if (empty($data)) {
            $this->logger->error('Datos recibidos vacíos', [
                'content' => $request->getContent(),
                'form_data' => $request->request->all(),
                'headers' => $request->headers->all(),
                'method' => $request->getMethod()
            ]);
            return new JsonResponse(['error' => 'No se recibieron datos'], 400);
        }

        // Validar campos obligatorios
        $required = ['rating', 'comentario', 'usuario_id', 'reserva_id','created_at'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $this->logger->error('Campo obligatorio faltante o vacío', ['campo' => $field, 'recibido' => $data]);
                return new JsonResponse(['error' => 'Campo obligatorio faltante o vacío: ' . $field], 400);
            }
        }

        // Validar rangos
        if (!is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return new JsonResponse(['error' => 'rating debe ser un número entre 1 y 5'], 400);
        }
        if (empty($data['comentario'])) {
            return new JsonResponse(['error' => 'El comentario no puede estar vacío'], 400);
        }

        // Buscar usuario y reserva
        $usuario = $em->getRepository(User::class)->find($data['usuario_id']);
        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }
        $reserva = $em->getRepository(Reservas::class)->find($data['reserva_id']);
        if (!$reserva) {
            return new JsonResponse(['error' => 'Reserva no encontrada'], 404);
        }

        // Crear y persistir la valoración
        try {
            $review = new Reviews();
            $review->setRating((int) $data['rating']);
            $review->setComment($data['comentario']);
            
            // Parsear la fecha si viene como string, o usar la fecha actual
            if (isset($data['created_at']) && !empty($data['created_at'])) {
                try {
                    $createdAt = new \DateTime($data['created_at']);
                } catch (\Exception $e) {
                    $createdAt = new \DateTime();
                }
            } else {
                $createdAt = new \DateTime();
            }
            $review->setCreatedAt($createdAt);
            
            $review->setUsuarioId($usuario);
            $review->setReservaId($reserva);
            
            // Establecer la relación inversa en Reservas
            $reserva->setReview($review);

            $em->persist($review);
            $em->persist($reserva);
            $em->flush();

            return new JsonResponse([
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comentario' => $review->getComment(),
                'fecha' => $review->getCreatedAt()->format(\DateTimeInterface::ISO8601),
                'usuario_id' => $review->getUsuarioId()->getId(),
                'reserva_id' => $review->getReservaId()->getId(),
            ], 201);
        } catch (\Exception $e) {
            $this->logger->error('Error al guardar la valoración', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            return new JsonResponse([
                'error' => 'Error al guardar la valoración',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ], 500);
        }
        
    }
    #[Route('/list', name: 'api_listar_valoraciones', methods: ['GET'])]
public function listar(EntityManagerInterface $em): JsonResponse
{
    $valoraciones = $em->getRepository(Reviews::class)->findAll();

    $data = [];
    foreach ($valoraciones as $valoracion) {
        if (!$valoracion->getCreatedAt() || !$valoracion->getUsuarioId()) {
            $this->logger->error('Valoración con datos inválidos', [
                'id' => $valoracion->getId(),
                'fecha' => $valoracion->getCreatedAt(),
                'usuario' => $valoracion->getUsuarioId()
            ]);
            continue;
        }
        $reserva = $valoracion->getReservaId();
        $usuario = $valoracion->getUsuarioId();
        $data[] = [
            'id' => $valoracion->getId(),
            'rating' => $valoracion->getRating(),
            'comentario' => $valoracion->getComment(),
            'fecha' => $valoracion->getCreatedAt()->format('Y-m-d'),
            'usuario_id' => $valoracion->getUsuarioId()->getId(),
            'reserva' => $reserva ? [
                'id' => $reserva->getId(),
                'status' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario' => $reserva->getUserId() ? $reserva->getUserId()->getId() : null,
                'vehicles' => $reserva->getVehicle() ? $reserva->getVehicle()->getId() : null,
                'vehicles_model' => $reserva->getVehicle() ? $reserva->getVehicle()->getModel() : null,
            ] : null,
            'usuario' => $usuario ? [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getName(),
                'apellidos' => $usuario->getLastName(),
                'email' => $usuario->getEmail(),
                'telefono' => $usuario->getTelefono(),
                'rol' => $usuario->getRol(),
                
            ] : null,
        ];
    }

    return new JsonResponse(['valoraciones' => $data], 200);
}

  #[Route('/{id<\d+>}', name: 'app_review_show', methods: ['GET'])]
public function show(int $id, ReviewsRepository $reviewRepository): JsonResponse
{
    $review = $reviewRepository->find($id);
    if (!$review) {
        return new JsonResponse(['error' => 'Valoracion no encontrada'], 404);
    }

    // ✅ Sin foreach, es un objeto único
    $reserva = $review->getReservaId();
    $usuario = $review->getUsuarioId();
    $data = [
            'id' => $review->getId(),
            'rating' => $review->getRating(),
            'comentario' => $review->getComment(),
            'fecha' => $review->getCreatedAt()->format('Y-m-d'),
            'usuario_id' => $review->getUsuarioId()->getId(),
            'reserva' => $reserva ? [
                'id' => $reserva->getId(),
                'status' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario' => $reserva->getUserId() ? $reserva->getUserId()->getId() : null,
                'vehicles' => $reserva->getVehicle() ? $reserva->getVehicle()->getId() : null,
                'vehicles_model' => $reserva->getVehicle() ? $reserva->getVehicle()->getModel() : null,
            ] : null,
            'usuario' => $usuario ? [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getName(),
                'apellidos' => $usuario->getLastName(),
                'email' => $usuario->getEmail(),
                'telefono' => $usuario->getTelefono(),
                'rol' => $usuario->getRol(),
                
            ] : null,
        ];

    return new JsonResponse($data);
}
  #[Route('/{id}/edit', name: 'app_review_edit', methods: ['GET', 'PUT'])]
public function edit(Request $request, int $id, ReviewsRepository $reviewRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $review = $reviewRepository->find($id);
    if (!$review) {
        return new JsonResponse(['error' => 'Review no encontrada'], 404);
    }

        if ($request->getMethod() === 'GET') {
            $data = [
            'id' => $review->getId(),
            'rating' => $review->getRating(),
            'comment' => $review->getComment(),
            'fecha' => $review->getCreatedAt()->format('Y-m-d'),
            'usuario_id' => $review->getUsuarioId()->getId(),
            'reserva_id' => $review->getReservaId()->getId(),

        ];

            
            return new JsonResponse($data);
        }
        
        // For PUT requests, update the reservation
        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
        
        
       if (isset($data['rating'])) {
            $review->setRating($data['rating']);
        }

        
       if (isset($data['comment'])) {
            $review->setComment($data['comment']);
        }
        
        $entityManager->flush();
        
        return new JsonResponse(['status' => 'Reserva actualizada']);
    }
#[Route('/delete/{id}', name: 'app_valoracion_delete', methods: ['GET','DELETE'])]
public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    $review = $entityManager->getRepository(Reviews::class)->find($id);
    if (!$review) {
        return new JsonResponse(['error' => 'Review no encontrada'], 404);
    }
    
    $entityManager->remove($review);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Review eliminada con éxito'], 200);
}
}
