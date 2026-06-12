<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


class FinancingController extends AbstractController
{
    #[Route('/api/financing/calculate', name: 'api_financing_calculate', methods: ['POST'])]
    public function calculate(Request $request): JsonResponse
    {
        // Obtener el JSON del frontend
        $data = json_decode($request->getContent(), true) ?? [];

        // Si no existen se asignan valores de fallback
        $precio = $data['precio'] ?? 0;
        $entrada = $data['entrada'] ?? 3000;
        $meses = $data['meses'] ?? 60;

        if ($precio <= 0 || $meses <= 0 || $entrada < 0) {
            return $this->json(['error' => 'Datos inválidos'], 400);
        }

        $cantidadFinanciar = $precio - $entrada;
        
        // Si la entrada es mayor o igual al precio, no hay nada que financiar
        if ($cantidadFinanciar <= 0) {
            return $this->json([
                'cuotaMensual' => 0,
                'cantidadFinanciar' => 0
            ]);
        }

        // TIN del 6.5%
        $tinDecimal = 0.065;
        $tinPorcentaje = 6.5;
        $tae = round(($tinDecimal + 0.012) * 100, 2);

        $tasaMensual = $tinDecimal / 12;

        if ($tasaMensual > 0) {
            // Fórmula de amortización francesa de préstamos
            $cuotaMensual = $cantidadFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $meses)) / (pow(1 + $tasaMensual, $meses) - 1);
        } else {
            $cuotaMensual = $cantidadFinanciar / $meses;
        }

        return $this->json([
            'precio' => $precio,
            'entrada' => $entrada,
            'meses' => $meses,
            'tin' => $tinPorcentaje,
            'tae' => $tae,
            'cantidadFinanciar' => $cantidadFinanciar,
            'cuotaMensual' => round($cuotaMensual, 2)
        ]);
    }
}
