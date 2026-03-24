<?php

namespace App\Controller;

use App\Repository\ReservasRepository;
use App\Repository\ReservasBorradasRepository;
use App\Repository\ReservasAnuladasRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservas;
use App\Entity\Vehicles;
use App\Entity\ReservasBorradas;
use App\Entity\ReservasAnuladas;
use App\Entity\Reviews;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Form\UsuariosType;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
#[Route('api/reservas')]
final class ReservasController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}
    

    #[Route(name: 'app_reservas_index', methods: ['GET'])]
    public function index(ReservasRepository $reservasRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $reservas = $reservasRepository->findAll();
        $data = [];
        
        foreach ($reservas as $reserva) {
            $review = $reserva->getReview();
            $valoracion = $review ? $review->getId() : null;
            $servicioRating = $review ? $review->getRating() : null;
            $comentario = $review ? $review->getComment() : null;
            $fecha = $review && $review->getCreatedAt() ? $review->getCreatedAt()->format('Y-m-d') : null;
            
            $vehiculo = $reserva->getVehicleId();
            $vehiculoId = $vehiculo ? $vehiculo->getId() : null;
            $vehiculoModel = $vehiculo ? $vehiculo->getModel() : null;

            $data[] = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser()?->getId(),

                'vehiculo' => $vehiculo ? [
                    'id' => $vehiculo->getId(),
                    'model' => $vehiculo->getModel(),
                    'marca' => $vehiculo->getMarca(),
                    'kms' => $vehiculo->getKm(),
                    'year' => $vehiculo->getYear(),
                    'image_url' => $vehiculo->getVehiclesImagesId()
    ->map(fn($img) => 'http://localhost:8000' . $img->getImageUrl())
    ->toArray() ?? null,
                ] : null,

    'valoracion' => $valoracion,
    'valoracion_comentario' => $comentario,
    'valoracion_servicio' => $servicioRating,
    'valoracion_fecha' => $fecha
];
        }
        
        return new JsonResponse($data);
    }
    #[Route('/filter', name: 'app_reservas_filter', methods: ['GET'])]
public function filter(Request $request, ReservasRepository $reservasRepository): JsonResponse
{
    $tipo = $request->query->get('tipo');
    $timezone = new \DateTimeZone('Europe/Madrid');
    $now = new \DateTime('now', $timezone);

    $this->logger->info('Fecha y hora actual: ' . $now->format('Y-m-d H:i:s'));

    if (!in_array($tipo, ['activas', 'expiradas'])) {
        return new JsonResponse(['error' => 'Tipo de filtro no válido'], 400);
    }

    $reservas = $reservasRepository->findAll();
    $data = [];

    foreach ($reservas as $reserva) {
        $fecha = $reserva->getDia();
        $hora = $reserva->getHora();
        $dateTimeString = ($fecha ? $fecha->format('Y-m-d') : 'null') . ' ' . ($hora ? $hora->format('H:i:s') : 'null');
        if (!$fecha || !$hora) {
            continue;
        }

        $fechaHoraReserva = new \DateTime($fecha->format('Y-m-d') . ' ' . $hora->format('H:i:s'), $timezone);
        
        $this->logger->info('Reserva ID ' . $reserva->getId() . ': ' . $fechaHoraReserva->format('Y-m-d H:i:s'));
        
        $esExpirada = $fechaHoraReserva <= $now;
        
        $this->logger->info('Reserva ID ' . $reserva->getId() . ' es expirada: ' . ($esExpirada ? 'Sí' : 'No'));
        $vehiculo = $reserva->getVehicleId();
        $vehiculoId = $vehiculo ? $vehiculo->getId() : null;
        $vehiculoModel = $vehiculo ? $vehiculo->getModel() : null;
        if (
            ($tipo === 'activas' && !$esExpirada) ||
            ($tipo === 'expiradas' && $esExpirada)
        ) {
            $valoracion = $reserva->getReview() ?: null;
             $data[] = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser() ? $reserva->getUser()->getId() : null,
                'vehiculo_id' => $vehiculoId,
                'vehiculo_model' => $vehiculoModel,
                'valoracion' => $valoracion ? $valoracion->getRating() : null,
                'valoracion_comentario' => $valoracion ? $valoracion->getComment() : null,
                'valoracion_model' => $valoracion && $reserva->getVehicle() ? $reserva->getVehicle()->getModel() : null,
                'valoracion_fecha' => $dateTimeString
            ];
        }
    }

    $this->logger->info('Total de reservas encontradas para ' . $tipo . ': ' . count($data));
    
    return new JsonResponse($data);
}
 #[Route('/usuario/{id}', name: 'app_reservas_by_usuario', methods: ['GET'])]
        public function reservasPorUsuario(int $id, ReservasRepository $reservasRepository, EntityManagerInterface $entityManager): JsonResponse
        {
            $reservas = $reservasRepository->findBy(['user' => $id]);
            $data = [];
        
        foreach ($reservas as $reserva) {
            $review = $reserva->getReview();
            $valoracion = $review ? $review->getId() : null;
            $servicioRating = $review ? $review->getRating() : null;
            $comentario = $review ? $review->getComment() : null;
            $fecha = $review && $review->getCreatedAt() ? $review->getCreatedAt()->format('Y-m-d') : null;
            
            $vehiculo = $reserva->getVehicleId();
            $data[] = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser()?->getId(),
               
                'vehiculo' => $vehiculo ? [
                    'id' => $vehiculo->getId(),
                    'model' => $vehiculo->getModel(),
                    'marca' => $vehiculo->getMarca(),
                    'kms' => $vehiculo->getKm(),
                    'year' => $vehiculo->getYear(),
 'image_url' => $vehiculo->getVehiclesImagesId()
        ->map(fn($img) => 'http://localhost:8000' . $img->getImageUrl())
        ->toArray() ?? null,                ] : null,

                'valoracion' => $valoracion,
                'valoracion_comentario' => $comentario,
                'valoracion_servicio' => $servicioRating,
                'valoracion_fecha' => $fecha
            ];
        }
        
        return new JsonResponse($data);
        }
     #[Route('/new', name: 'app_reservas_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        ReservasRepository $reservasRepository,
        //ReservasBorradasRepository $reservasBorradasRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true);
    
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
    
        $requiredFields = ['estado', 'dia', 'hora', 'usuario_id','vehicle_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse(['status' => "El campo '$field' es obligatorio"], 400);
            }
        }
    
        // Validar fecha
        $dia = \DateTime::createFromFormat('Y-m-d', $data['dia']);
        if (!$dia) {
            return new JsonResponse(['status' => 'Formato de fecha inválido (Y-m-d)'], 400);
        }
    
        // Validar hora
        $hora = \DateTime::createFromFormat('H:i', $data['hora']);
        if (!$hora) {
            return new JsonResponse(['status' => 'Formato de hora inválido (H:i)'], 400);
        }
    
        // Validar usuario
        $usuario = $entityManager->getRepository(User::class)->find($data['usuario_id']);
        $vehiculo = $entityManager->getRepository(Vehicles::class)->find($data['vehicle_id']);

        if (!$usuario) {
            return new JsonResponse(['status' => 'Usuario no encontrado'], 404);
        }
        if (!$vehiculo) {
            return new JsonResponse(['status' => 'Vehículo no encontrado'], 404);
        }
    
        $reserva = new Reservas();
        
        $reserva->setStatus($data['estado']);
        $reserva->setDia($dia);
        $reserva->setHora($hora);
        $reserva->setUser($usuario);
        $reserva->setCreatedAt(new \DateTime());
        $reserva->setVehicle($vehiculo);
    
        // Asegurar que el vehículo también se persista para guardar la relación
        $entityManager->persist($reserva);
        $entityManager->persist($vehiculo);
        $entityManager->flush();

        try {
            $email = (new Email())
            ->from('marcosvalle@gmail.com')
            ->to($usuario->getEmail())
            ->subject('Confirmación de Reserva de Furgoneta 🚐')
            ->html(
                '<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">

<!-- HEADER -->
<tr>
<td style="background:#111827; padding:30px; text-align:center;">
<h1 style="color:#ffffff; margin:0; font-size:24px;">
🚐 FlexemCar
</h1>
<p style="color:#9ca3af; margin:5px 0 0 0; font-size:14px;">
Confirmación de Reserva
</p>
</td>
</tr>

<!-- BODY -->
<tr>
<td style="padding:35px;">

<h2 style="color:#111827; margin-top:0;">
¡Tu reserva ha sido confirmada!
</h2>

<p style="color:#4b5563; font-size:15px;">
Hola <strong>' . htmlspecialchars($usuario->getName()) . '</strong>,
</p>

<p style="color:#4b5563; font-size:15px;">
Hemos registrado correctamente tu reserva. A continuación puedes ver todos los detalles:
</p>

<!-- DETALLES -->
<table width="100%" cellpadding="10" cellspacing="0" style="margin-top:20px; border-collapse:collapse;">

<tr style="background:#f9fafb;">
<td style="font-weight:bold;">Vehículo</td>
<td>' . htmlspecialchars($vehiculo->getMarca()) . ' ' . htmlspecialchars($vehiculo->getModel()) . '</td>
</tr>

<tr>
<td style="font-weight:bold;">Precio</td>
<td>' . number_format($vehiculo->getPrice(), 2) . ' €</td>
</tr>

<tr style="background:#f9fafb;">
<td style="font-weight:bold;">Fecha</td>
<td>' . $reserva->getDia()->format("d/m/Y") . '</td>
</tr>

<tr>
<td style="font-weight:bold;">Hora</td>
<td>' . $reserva->getHora()->format("H:i") . '</td>
</tr>

<tr style="background:#f9fafb;">
<td style="font-weight:bold;">Estado</td>
<td style="color:#16a34a; font-weight:bold;">' . htmlspecialchars($reserva->getStatus()) . '</td>
</tr>

</table>

<!-- CTA -->
<div style="text-align:center; margin:35px 0;">
<a href="https://wa.me/34600000000"
style="
background:#111827;
color:#ffffff;
padding:14px 28px;
text-decoration:none;
border-radius:6px;
font-weight:bold;
display:inline-block;
font-size:14px;
">
Contactar con Asesor
</a>
</div>

<p style="color:#6b7280; font-size:13px; text-align:center;">
Si necesitas modificar o cancelar tu reserva, ponte en contacto con nosotros lo antes posible.
</p>

</td>
</tr>

<!-- FOOTER -->
<tr>
<td style="background:#f3f4f6; padding:20px; text-align:center; font-size:12px; color:#6b7280;">
© ' . date("Y") . ' FlexemCar - Todos los derechos reservados<br>
info@flexemcar.com | +34 600 000 000
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
');

        $mailer->send($email);
            $this->logger->info('Email de confirmación enviado correctamente a ' . $usuario->getEmail());
        } catch (\Exception $e) {
            $this->logger->error('Error al enviar el email de confirmación: ' . $e->getMessage());
        }
        
        // Respuesta al frontend, incluyendo el código si se ha generado
        return new JsonResponse([
            'status' => 'Reserva creada',
            'reserva_id' => $reserva->getId(),
            'usuario_id' => $usuario->getId(),
            'vehiculo_id' => $vehiculo->getId(),
            'modelo_vehiculo' => $vehiculo->getModel(),
            'dia' => $reserva->getDia()->format('Y-m-d'),
            'hora' => $reserva->getHora()->format('H:i'),
            'estado' => $reserva->getStatus()
        ], 201);
    }

      #[Route('/delete/{id}', name: 'app_reservas_delete_id', methods: ['GET','DELETE'])]
    public function delete(int $id, ReservasRepository $reservasRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): JsonResponse
    {
        $reserva = $reservasRepository->find($id);
        if (!$reserva) {
            return new JsonResponse(['status' => 'Reserva no encontrada'], 404);
        }
    
        try {
            // Verificar si la reserva ha expirado
            $timezone = new \DateTimeZone('Europe/Madrid');
            $now = new \DateTime('now', $timezone);
            
            $fecha = $reserva->getDia();
            $hora = $reserva->getHora();
            
            if (!$fecha || !$hora) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'La reserva no tiene fecha u hora válida'
                ], 400);
            }
            
            $fechaHoraReserva = new \DateTime($fecha->format('Y-m-d') . ' ' . $hora->format('H:i:s'), $timezone);
            
            // Obtener el vehículo asociado antes de eliminar la reserva
            $vehiculo = $reserva->getVehicleId();
            
            // Obtener la review asociada (si existe) para desvincularla antes de eliminar
            $review = $reserva->getReview();

            // Ver si la reserva ha expirado
            $haExpirado = $fechaHoraReserva <= $now;
            
            // Si la reserva ha expirado, moverla a ReservasBorradas
            if ($haExpirado) {
                // Verificar si el vehículo ya está asociado a otra reserva borrada
                $vehiculoParaBorrada = null;
                if ($vehiculo) {
                    $reservaBorradaExistente = $entityManager->getRepository(ReservasBorradas::class)
                        ->findOneBy(['vehicle_id' => $vehiculo]);
                    
                    // Solo asignar el vehículo si no está ya asociado a otra reserva borrada
                    if (!$reservaBorradaExistente) {
                        $vehiculoParaBorrada = $vehiculo;
                    }
                }
                
                $reservaBorrada = ReservasBorradas::fromReserva($reserva);
                // Sobrescribir el vehículo si ya está asociado a otra reserva borrada
                if ($vehiculoParaBorrada === null && $vehiculo) {
                    $reservaBorrada->setVehicleId(null);
                }
                $entityManager->persist($reservaBorrada);
                
                // Desvincular la review de la reserva antes de eliminarla (para que no se borre en cascada)
                if ($review) {
                    $reserva->setReview(null);
                    // También desvincular la reserva de la review
                    $review->setReservaId(null);
                    $entityManager->persist($review);
                }
                
                // Eliminar de la tabla reserva y persistir los cambios
                $entityManager->remove($reserva);
                $entityManager->flush();
                
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Reserva expirada movida a reservas borradas',
                    'reserva_borrada_id' => $reservaBorrada->getId(),
                    'usuario_id' => $reservaBorrada->getUserId() ? $reservaBorrada->getUserId()->getId() : null
                ]);
            }
            
            // Si la reserva está activa, moverla a ReservasAnuladas
            $usuario = $reserva->getUser();
            
            // Verificar si el vehículo ya está asociado a otra reserva anulada
            $vehiculoParaAnulada = null;
            if ($vehiculo) {
                $reservaAnuladaExistente = $entityManager->getRepository(ReservasAnuladas::class)
                    ->findOneBy(['vehicle_id' => $vehiculo]);
                
                // Solo asignar el vehículo si no está ya asociado a otra reserva anulada
                if (!$reservaAnuladaExistente) {
                    $vehiculoParaAnulada = $vehiculo;
                }
            }
            
            $reservaAnulada = new ReservasAnuladas();
            $reservaAnulada->setStatus('anulada');
            $reservaAnulada->setDia($reserva->getDia());
            $reservaAnulada->setHora($reserva->getHora());
            $reservaAnulada->setUserId($usuario);
            $reservaAnulada->setVehicleId($vehiculoParaAnulada);
            $reservaAnulada->setFechaAnulada(new \DateTime());

            $entityManager->persist($reservaAnulada);
            
            // Desvincular la review de la reserva antes de eliminarla (para que no se borre en cascada)
            if ($review) {
                $reserva->setReview(null);
                // También desvincular la reserva de la review
                $review->setReservaId(null);
                $entityManager->persist($review);
            }
            
            // Eliminar de la tabla reserva y persistir los cambios
            $entityManager->remove($reserva);
            $entityManager->flush();
            
            // Enviar email solo si hay un usuario asociado
            if ($usuario && $usuario->getEmail()) {
                try {
                    $email = (new Email())
                        ->from('marcosvalleu@gmail.com')
                        ->to($usuario->getEmail())
                        ->subject('Reserva Anulada - HairBooking')
                        ->html(
                            '<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:Arial, sans-serif;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
                                <tr>
                                <td align="center">

                                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">

                                <!-- HEADER -->
                                <tr>
                                <td style="background:#111827; padding:30px; text-align:center;">
                                <h1 style="color:#ffffff; margin:0; font-size:24px;">
                                🚐 FlexemCar
                                </h1>
                                <p style="color:#9ca3af; margin:5px 0 0 0; font-size:14px;">
                                Reserva anulada
                                </p>
                                </td>
                                </tr>

                                <!-- BODY -->
                                <tr>
                                <td style="padding:35px;">

                                <h2 style="color:#b91c1c; margin-top:0;">
                                Tu reserva ha sido cancelada
                                </h2>

                                <p style="color:#4b5563; font-size:15px;">
                                Hola <strong>' . htmlspecialchars($usuario->getName()) . '</strong>,
                                </p>

                                <p style="color:#4b5563; font-size:15px;">
                                Te informamos que tu reserva ha sido anulada correctamente. 
                                A continuación puedes consultar los detalles:
                                </p>

                                <!-- DETALLES -->
                                <table width="100%" cellpadding="10" cellspacing="0" style="margin-top:20px; border-collapse:collapse;">

                                <tr style="background:#f9fafb;">
                                <td style="font-weight:bold;">Vehículo</td>
                                <td>' . ($vehiculo ? (htmlspecialchars($vehiculo->getMarca()) . ' ' . htmlspecialchars($vehiculo->getModel())) : 'N/A') . '</td>
                                </tr>

                                <tr>
                                <td style="font-weight:bold;">Precio</td>
                                <td>' . ($vehiculo ? number_format($vehiculo->getPrice(), 2) . ' €' : 'N/A') . '</td>
                                </tr>

                                <tr style="background:#f9fafb;">
                                <td style="font-weight:bold;">Fecha</td>
                                <td>' . $reserva->getDia()->format("d/m/Y") . '</td>
                                </tr>

                                <tr>
                                <td style="font-weight:bold;">Hora</td>
                                <td>' . $reserva->getHora()->format("H:i") . '</td>
                                </tr>

                                <tr style="background:#f9fafb;">
                                <td style="font-weight:bold;">Estado</td>
                                <td style="color:#b91c1c; font-weight:bold;">ANULADA</td>
                                </tr>

                                </table>

                                <!-- CTA -->
                                <div style="text-align:center; margin:35px 0;">
                                <a href="https://flexemcar.com/catalogo"
                                style="
                                background:#111827;
                                color:#ffffff;
                                padding:14px 28px;
                                text-decoration:none;
                                border-radius:6px;
                                font-weight:bold;
                                display:inline-block;
                                font-size:14px;
                                ">
                                Ver otros vehículos disponibles
                                </a>
                                </div>

                                <p style="color:#6b7280; font-size:13px; text-align:center;">
                                Si tienes cualquier duda o deseas realizar una nueva reserva, nuestro equipo estará encantado de ayudarte.
                                </p>

                                </td>
                                </tr>

                                <!-- FOOTER -->
                                <tr>
                                <td style="background:#f3f4f6; padding:20px; text-align:center; font-size:12px; color:#6b7280;">
                                © ' . date("Y") . ' FlexemCar - Todos los derechos reservados<br>
                                info@flexemcar.com | +34 600 000 000
                                </td>
                                </tr>

                                </table>

                                </td>
                                </tr>
                                </table>

                                </body>
                                </html>
                                ');
                    
                    $mailer->send($email);
                    $this->logger->info('Email de anulación enviado correctamente a ' . $usuario->getEmail());
                } catch (\Exception $e) {
                    $this->logger->error('Error al enviar el email de anulación: ' . $e->getMessage());
                }
            }
            
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Reserva anulada y registrada correctamente',
                'reserva_id' => $reserva->getId()
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error al eliminar la reserva: ' . $e->getMessage());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al eliminar la reserva: ' . $e->getMessage()
            ], 500);
        }

        
    }
    
    

       #[Route('/{id<\d+>}', name: 'app_reservas_show', methods: ['GET'])]
public function show(int $id, ReservasRepository $reservasRepository): JsonResponse
{
    $reserva = $reservasRepository->find($id);
    if (!$reserva) {
        return new JsonResponse(['error' => 'Reserva no encontrada'], 404);
    }

    $review = $reserva->getReview();
    $valoracion = $review ? $review->getId() : null;
    $servicioRating = $review ? $review->getRating() : null;
    $comentario = $review ? $review->getComment() : null;
    $fecha = $review && $review->getCreatedAt() ? $review->getCreatedAt()->format('Y-m-d') : null;

    $vehiculo = $reserva->getVehicleId();

    $data = [
        'id' => $reserva->getId(),
        'estado' => $reserva->getStatus(),
        'dia' => $reserva->getDia()->format('Y-m-d'),
        'hora' => $reserva->getHora()->format('H:i'),
        'usuario_id' => $reserva->getUser()?->getId(),
        'vehiculo' => $vehiculo ? [
            'id' => $vehiculo->getId(),
            'model' => $vehiculo->getModel(),
            'marca' => $vehiculo->getMarca(),
            'kms' => $vehiculo->getKm(),
            'year' => $vehiculo->getYear(),
            'image_url' => $vehiculo->getVehiclesImagesId()  // ← muévelo aquí dentro
        ->map(fn($img) => 'http://localhost:8000' . $img->getImageUrl())
        ->toArray(),

        ] : null,
        'valoracion' => $valoracion,
        'valoracion_comentario' => $comentario,
        'valoracion_servicio' => $servicioRating,
        'valoracion_fecha' => $fecha
    ];

    return new JsonResponse($data);
}
    #[Route('/{id}/edit', name: 'app_reservas_edit', methods: ['GET', 'PUT'])]
public function edit(Request $request, int $id, ReservasRepository $reservasRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $reserva = $reservasRepository->find($id);
    if (!$reserva) {
        return new JsonResponse(['error' => 'Reserva no encontrada'], 404);
    }
    $vehiculo = $reserva->getVehicleId();
    $review = $reserva->getReview();
    $valoracion = $review ? $review->getId() : null;
    $servicioRating = $review ? $review->getRating() : null;
    $comentario = $review ? $review->getComment() : null;
    $fecha = $review && $review->getCreatedAt() ? $review->getCreatedAt()->format('Y-m-d') : null;

        if ($request->getMethod() === 'GET') {
            $data = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser()?->getId(),
                'vehiculo' => $vehiculo ? [
                    'id' => $vehiculo->getId(),
                    'model' => $vehiculo->getModel(),
                    'marca' => $vehiculo->getMarca(),
                    'kms' => $vehiculo->getKm(),
                    'year' => $vehiculo->getYear(),
                    'image' => $vehiculo->getVehiclesImagesId(),
                ] : null,

                'valoracion' => $valoracion,
                'valoracion_comentario' => $comentario,
                'valoracion_servicio' => $servicioRating,
                'valoracion_fecha' => $fecha
            ];
            
            return new JsonResponse($data);
        }
        
        // For PUT requests, update the reservation
        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
        

        if (isset($data['dia'])) {
            try {
                $dia = new \DateTime($data['dia']);
                $reserva->setDia($dia);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'Formato de fecha inválido'], 400);
            }
        }
        
        if (isset($data['hora'])) {
            try {
                $hora = \DateTime::createFromFormat('H:i', $data['hora']);
                if ($hora === false) {
                    return new JsonResponse(['status' => 'Formato de hora inválido'], 400);
                }
                $reserva->setHora($hora);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'Formato de hora inválido'], 400);
            }
        }
        
        if (isset($data['usuario_id'])) {
            $usuario = $entityManager->getRepository(User::class)->find($data['usuario_id']);
            if (!$usuario) {
                return new JsonResponse(['status' => 'Usuario no encontrado'], 404);
            }
            $reserva->setUser($usuario);
        }
        
        $entityManager->flush();
        
        return new JsonResponse(['status' => 'Reserva actualizada']);
    }

    #[Route('/usuario/{id}/count', methods: ['GET'])]
        public function countReservasPorUsuario(
            int $id,
            ReservasRepository $reservasRepository,
            ReservasBorradasRepository $reservasBorradasRepository,
            EntityManagerInterface $entityManager
        ): JsonResponse {
            $total = $reservasRepository->countByUsuarioId($id)
                   + $reservasBorradasRepository->countByUsuarioId($id);
        
            $usuario = $entityManager->getRepository(User::class)->find($id);
        
        
            return $this->json([
                'usuarioId' => $id,
                'totalReservas' => $total
            ]);
        }
}
