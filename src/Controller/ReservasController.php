<?php

namespace App\Controller;

use App\Repository\ReservasRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Reservas;
use App\Entity\Vehicles;
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
    public function index(ReservasRepository $reservasRepository): JsonResponse
    {
        $reservas = $reservasRepository->findAll();
        $data = [];
        
        foreach ($reservas as $reserva) {
            $valoracion = $reserva->getReview()->first() ? $reserva->getReview()->first()->getId() : null;
            $servicioRating = $reserva->getReview()->first() ? $reserva->getReview()->first()->getRating() : null;
            $comentario = $reserva->getReview()->first() ? $reserva->getReview()->first()->getComment() : null;
            $fecha = $reserva->getReview()->first() ? $reserva->getReview()->first()->getDate()->format('Y-m-d') : null;
            $vehiculoId = $reserva->getVehiclesId()->first() ? $reserva->getVehiclesId()->first()->getId() : null;
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
    
        $entityManager->persist($reserva);
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
}
