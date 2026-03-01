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
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            // getReview() devuelve un objeto único o null (OneToOne), no una Collection
            $review = $reserva->getReview();
            $valoracion = $review ? $review->getId() : null;
            $servicioRating = $review ? $review->getRating() : null;
            $comentario = $review ? $review->getComment() : null;
            $fecha = $review && $review->getDate() ? $review->getDate()->format('Y-m-d') : null;
            
            // Obtener el vehículo asociado a esta reserva
            $vehicles = $reserva->getVehiclesId();
            $vehiculoId = null;
            if (!$vehicles->isEmpty()) {
                $vehiculoId = $vehicles->first()->getId();
            } else {
                // Si la Collection está vacía, buscar directamente en la BD por reserva_id
                $vehiculo = $entityManager->getRepository(Vehicles::class)->findOneBy(['reserva_id' => $reserva]);
                $vehiculoId = $vehiculo ? $vehiculo->getId() : null;
            }
            $data[] = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser() ? $reserva->getUser()->getId() : null,
                'vehiculo_id' => $vehiculoId,
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
        $reserva->setVehiclesId($vehiculo);
    
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

      #[Route('/usuario/{id}', name: 'app_reservas_by_usuario', methods: ['GET'])]
        public function reservasPorUsuario(int $id, ReservasRepository $reservasRepository, EntityManagerInterface $entityManager): JsonResponse
        {
            $reservas = $reservasRepository->findBy(['user' => $id]);
            $data = [];
        
        foreach ($reservas as $reserva) {
            // getReview() devuelve un objeto único o null (OneToOne), no una Collection
            $review = $reserva->getReview();
            $valoracion = $review ? $review->getId() : null;
            $servicioRating = $review ? $review->getRating() : null;
            $comentario = $review ? $review->getComment() : null;
            $fecha = $review && $review->getDate() ? $review->getDate()->format('Y-m-d') : null;
            
            // Obtener el vehículo asociado a esta reserva
            $vehicles = $reserva->getVehiclesId();
            $vehiculoId = null;
            if (!$vehicles->isEmpty()) {
                $vehiculoId = $vehicles->first()->getId();
            } else {
                // Si la Collection está vacía, buscar directamente en la BD por reserva_id
                $vehiculo = $entityManager->getRepository(Vehicles::class)->findOneBy(['reserva_id' => $reserva]);
                $vehiculoId = $vehiculo ? $vehiculo->getId() : null;
            }
            $data[] = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser() ? $reserva->getUser()->getId() : null,
                'vehiculo_id' => $vehiculoId,
                'valoracion' => $valoracion,
                'valoracion_comentario' => $comentario,
                'valoracion_servicio' => $servicioRating,
                'valoracion_fecha' => $fecha
            ];
        }
        
        return new JsonResponse($data);
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
            
            // Ver si la reserva ha expirado
            $haExpirado = $fechaHoraReserva <= $now;
            
            // Si la reserva ha expirado, moverla a ReservasBorradas
            if ($haExpirado) {
                $reservaBorrada = ReservasBorradas::fromReserva($reserva);
                $entityManager->persist($reservaBorrada);
                $entityManager->remove($reserva);
                $entityManager->flush();
                
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Reserva expirada movida a reservas borradas',
                    'reserva_borrada_id' => $reservaBorrada->getId(),
                    'usuario_id' => $reservaBorrada->getUsuario()->getId()
                ]);
            }
            
            // Si la reserva está activa (no ha expirado), moverla a ReservasAnuladas
            $usuario = $reserva->getUsuario();
            
            $reservaAnulada = new ReservasAnuladas();
            $reservaAnulada->setStatus('anulada');
            $reservaAnulada->setDia($reserva->getDia());
            $reservaAnulada->setHora($reserva->getHora());
            $reservaAnulada->setUserId($usuario);
            $reservaAnulada->setVehicleId($reserva->getVehiclesId()->first() ?? null);
            $reservaAnulada->setFechaAnulada(new \DateTime());

            $entityManager->persist($reservaAnulada);
            $entityManager->flush();
            # Despues eliminarla de la tabla reserva
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
                                <td>' . htmlspecialchars($reserva->getVehiclesId()->getMarca()) . ' ' . htmlspecialchars($reserva->getVehiclesId()->getModel()) . '</td>
                                </tr>

                                <tr>
                                <td style="font-weight:bold;">Precio</td>
                                <td>' . number_format($reserva->getVehiclesId()->getPrice(), 2) . ' €</td>
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

        if (!$fecha || !$hora) {
            continue;
        }

        // Crear DateTime para la reserva
        $fechaHoraReserva = new \DateTime($fecha->format('Y-m-d') . ' ' . $hora->format('H:i:s'), $timezone);
        
        $this->logger->info('Reserva ID ' . $reserva->getId() . ': ' . $fechaHoraReserva->format('Y-m-d H:i:s'));
        
        // Comparación simple
        $esExpirada = $fechaHoraReserva <= $now;
        
        $this->logger->info('Reserva ID ' . $reserva->getId() . ' es expirada: ' . ($esExpirada ? 'Sí' : 'No'));

        if (
            ($tipo === 'activas' && !$esExpirada) ||
            ($tipo === 'expiradas' && $esExpirada)
        ) {
            $valoracion = $reserva->getValoracions()->first() ?: null;
             $data[] = [
                'id' => $reserva->getId(),
                'estado' => $reserva->getStatus(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUser() ? $reserva->getUser()->getId() : null,
                'vehiculo_id' => $vehiculoId,
                'valoracion' => $valoracion,
                'valoracion_comentario' => $comentario,
                'valoracion_servicio' => $servicioRating,
                'valoracion_fecha' => $fecha
            ];
        }
    }

    $this->logger->info('Total de reservas encontradas para ' . $tipo . ': ' . count($data));
    
    return new JsonResponse($data);
}
    
}
