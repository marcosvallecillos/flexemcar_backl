<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ReservasAnuladasController extends AbstractController
{
    #[Route('/reservas/anuladas', name: 'app_reservas_anuladas')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ReservasAnuladasController.php',
        ]);
    }
}
