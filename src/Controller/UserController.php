<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Reservas;
use App\Entity\Rewiews;
use App\Entity\Favorites;
use App\Entity\Vehicles;
use Symfony\Component\Mailer\MailerInterface;
use App\Form\UsuariosType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/api/usuarios')]
final class UserController extends AbstractController
{
     private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route(name: 'app_usuarios_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $usuarios = $userRepository->findAll();
        if (!$usuarios) {
            return new JsonResponse(['message' => 'No se encontraron usuarios'], Response::HTTP_NOT_FOUND);
        }
        $data = [];
        
        foreach ($usuarios as $usuario) {
            $data[] = [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getName(),
                'apellidos' => $usuario->getLastName(),
                'email' => $usuario->getEmail(),
                'telefono' => $usuario->getTelefono(),
                'rol' => $usuario->getRol(),
                'reservas' => $usuario->getReservas()->map(function($reserva) {
                    return [
                        'id' => $reserva->getId(),
                        'vehiculo' => $reserva->getVehicleId()->getModel(),
                        'fecha' => $reserva->getDate(),
                        'estado' => $reserva->getStatus()
                    ];
                })->toArray()
            ];
        }
        
        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_usuarios_new', methods: ['GET','POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $data = json_decode($request->getContent(), true);
    
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
    
        if (empty($data['password'])) {
            return new JsonResponse(['status' => 'El password es obligatorio'], 400);
        }

        // Validar teléfono: exactamente 9 dígitos numéricos
        if (!isset($data['telefono']) || !preg_match('/^\d{9}$/', (string) $data['telefono'])) {
            return new JsonResponse(['status' => 'El teléfono debe tener 9 dígitos'], 400);
        }
    
    
        $usuario = new User();
        $usuario->setName($data['nombre'] ?? null);
        $usuario->setLastName($data['apellidos'] ?? null);
        $usuario->setEmail($data['email'] ?? null);
        $hashedPassword = $passwordHasher->hashPassword(
            $usuario,
            $data['password']
        );

        $usuario->setPassword($hashedPassword);
        $usuario->setTelefono($data['telefono'] ?? null);
        $usuario->setRol($data['rol'] ?? null);
        $entityManager->persist($usuario);
        $entityManager->flush();
        try {
            $email = (new Email())
                ->from('marcosvalleu@gmail.com')
                ->to($usuario->getEmail())
                ->subject('¡Bienvenido a FlexemCar! 🚐')
                ->html(
                    '<div style="font-family: Arial, sans-serif; line-height:1.6;">
                        <h2 style="color:#2c3e50;">¡Bienvenido a FlexemCar!</h2>

                        <p>Hola <strong>' . htmlspecialchars($usuario->getName()) . '</strong>,</p>

                        <p>Gracias por registrarte en nuestra plataforma de compra y venta de furgonetas.</p>

                        <p>Ahora puedes:</p>
                        <ul>
                            <li>Explorar nuestro catálogo actualizado</li>
                            <li>Guardar tus vehículos favoritos</li>
                            <li>Solicitar información personalizada</li>
                            <li>Reservar una furgoneta fácilmente</li>
                        </ul>

                        <p>Si quieres atención directa y rápida, nuestro equipo puede asesorarte por WhatsApp.</p>

                        <div style="margin:30px 0;">
                            <a href="https://wa.me/34600000000/text=Hola" target="_blank"
                            style="background-color:#25D366;
                                    color:white;
                                    padding:15px 25px;
                                    text-decoration:none;
                                    border-radius:5px;
                                    font-weight:bold;">
                            Hablar con un asesor por WhatsApp
                            </a>
                        </div>

                        <p>Estamos aquí para ayudarte a encontrar la furgoneta perfecta para tu negocio o uso personal.</p>

                        <p>Un saludo,<br>
                        <strong>Equipo FlexemCar</strong></p>
                    </div>'
                );

            $mailer->send($email);
            $this->logger->info('Email de confirmación enviado correctamente a ' . $usuario->getEmail());

            return new JsonResponse(['status' => 'Usuario creado', 'correo_enviado' => true], 201);
        } catch (\Exception $e) {
            $this->logger->error('Error al enviar el email de confirmación: ' . $e->getMessage());

            // El usuario se ha creado, pero el correo ha fallado
            return new JsonResponse([
                'status' => 'Usuario creado, pero el correo falló',
                'correo_enviado' => false,
                'error_email' => 'No se pudo enviar el email de bienvenida'
            ], 201);
        }
    }

    #[Route('/{id}', name: 'app_usuarios_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(User $usuario): JsonResponse
    {
        $data = [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getName(),
            'apellidos' => $usuario->getLastName(),
            'email' => $usuario->getEmail(),
            'telefono' => $usuario->getTelefono(),
            'rol'=> $usuario->getRol(),
            'reservas' => $usuario->getReservas()->map(function($reserva) {
                return [
                        'id' => $reserva->getId(),
                        'vehiculo' => $reserva->getVehicleId()->getModel(),
                        'fecha' => $reserva->getDate(),
                        'estado' => $reserva->getStatus()
                    ];
            })->toArray()
        ];
        
        return new JsonResponse($data);
    }
    
    #[Route('/login', name: 'app_usuarios_login', methods: ['GET','POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Verificamos que los datos necesarios estén presentes
        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse([
                'status' => 'bad',
                'message' => 'Faltan datos requeridos'
            ], 400);
        }

        $email = $data['email'];
        $password = $data['password'];

        // Buscamos el usuario por email
        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse([
                'status' => 'bad',
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse([
                'status' => 'bad',
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $result = [
            'status' => 'ok',
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nombre' => $user->getName(),
            'apellidos' => $user->getLastName(),
            'telefono' => $user->getTelefono(),
            'rol' => $user->getRol(),
        ];

        return new JsonResponse($result);
    }

        #[Route('/{id}/edit', methods: ['GET', 'PUT'], name: 'app_usuarios_edit')]
        public function edit(Request $request, User $usuario, EntityManagerInterface $entityManager): JsonResponse
        {
            // If it's a GET request, return the user data
            if ($request->getMethod() === 'GET') {
                $data = [
                    'id' => $usuario->getId(),
                    'nombre' => $usuario->getName(),
                    'apellidos' => $usuario->getLastName(),
                    'email' => $usuario->getEmail(),
                    'telefono' => $usuario->getTelefono(),
                    'password' => $usuario->getPassword(),
                ];
                
                return new JsonResponse($data);
            }
            
            // For PUT requests, update the user
            $data = json_decode($request->getContent(), true); // Se recibe la información en JSON.

            // Actualizamos los campos del usuario con los datos recibidos
            $usuario->setName($data['nombre'] ?? $usuario->getName());
            $usuario->setLastName($data['apellidos'] ?? $usuario->getLastName());
            $usuario->setEmail($data['email'] ?? $usuario->getEmail());
            $usuario->setTelefono($data['telefono'] ?? $usuario->getTelefono());
            $usuario->setPassword($data['password'] ?? $usuario->getPassword());
            

            $entityManager->flush();

            return new JsonResponse(['status' => 'Usuario actualizado']);
        }

        #[Route('/delete/{id}', name: 'app_usuarios_delete', methods: ['DELETE'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $usuario = $userRepository->find($id);
            if (!$usuario) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
            }

            $entityManager->remove($usuario);
            $entityManager->flush();

            return new JsonResponse(['status' => 'Usuario eliminado'], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Error al eliminar el usuario', 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/search', name: 'app_usuarios_search', methods: ['GET'])]
    public function search(Request $request, UserRepository $UserRepository): JsonResponse
    {
        try {
            $nombre = trim((string) $request->query->get('nombre', ''));
            $apellidos = trim((string) $request->query->get('apellidos', ''));
            if ($nombre === '') {
                return new JsonResponse(['error' => 'El parámetro "nombre" es requerido'], 400);
            }
            if ($apellidos === '') {
                return new JsonResponse(['error' => 'El parámetro "apellidos" es requerido'], 400);
            }

            $usuarios = $UserRepository->findByNombre($nombre);
            $data = [];

            foreach ($usuarios as $usuario) {
                $data[] = [
                    'id' => $usuario->getId(),
                    'nombre' => $usuario->getName(),
                    'apellidos' => $usuario->getLastName(),
                    'email' => $usuario->getEmail(),
                    'telefono' => $usuario->getTelefono(),
                ];
            }

            return new JsonResponse($data, 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Error en búsqueda', 'message' => $e->getMessage()], 500);
        }
    }
   
}
