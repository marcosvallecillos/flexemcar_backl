<?php

namespace App\Controller;

use App\Entity\VehiclesImages;
use App\Repository\VehiclesRepository;
use App\Repository\VehiclesImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vehicles-images')]
class VehiclesImagesController extends AbstractController
{
    #[Route('', name: 'vehicles_images_all', methods: ['GET'])]
    public function getAllImages(
        VehiclesImagesRepository $vehiclesImagesRepository
    ): JsonResponse {
        $images = $vehiclesImagesRepository->findAll();

        $data = array_map(fn($img) => [
            'id'        => $img->getId(),
            'vehicleId' => $img->getVehicleId()?->getId(),
            'image_url' => $img->getImageUrl(),
        ], $images);

        return $this->json($data);
    }

    #[Route('/by-vehicle/{vehicleId}', name: 'vehicles_images_by_vehicle', methods: ['GET'])]
    public function getImagesByVehicle(
        int $vehicleId,
        VehiclesRepository $vehiclesRepository,
        VehiclesImagesRepository $vehiclesImagesRepository
    ): JsonResponse {
        $vehicle = $vehiclesRepository->find($vehicleId);

        if (!$vehicle) {
            return $this->json(['error' => 'Vehículo no encontrado'], 404);
        }

        $images = $vehiclesImagesRepository->findBy(['vehicle_id' => $vehicle]);

        $data = array_map(fn($img) => [
            'id'        => $img->getId(),
            'image_url' => $img->getImageUrl(),
        ], $images);

        return $this->json($data);
    }

    #[Route('/new/{vehicleId}', name: 'vehicles_images_upload', methods: ['POST'])]
    public function uploadImage(
        int $vehicleId,
        Request $request,
        VehiclesRepository $vehiclesRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $vehicle = $vehiclesRepository->find($vehicleId);

        if (!$vehicle) {
            return $this->json(['error' => 'Vehículo no encontrado'], 404);
        }

        $file = $request->files->get('image');

        if (!$file) {
            return $this->json(['error' => 'No se recibió ningún archivo'], 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return $this->json(['error' => 'Tipo de archivo no permitido'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/vehicles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = uniqid('vehicle_') . '.' . $file->guessExtension();
        $file->move($uploadDir, $filename);

        $imageUrl = '/images/vehicles/' . $filename;

        $vehicleImage = new VehiclesImages();
        $vehicleImage->setVehicleId($vehicle);
        $vehicleImage->setImageUrl($imageUrl);

        $em->persist($vehicleImage);
        $em->flush();

        return $this->json([
            'id'        => $vehicleImage->getId(),
            'vehicleId' => $vehicleId,
            'image_url' => $imageUrl,
        ], 201);
    }

    #[Route('/delete/{id}', name: 'vehicles_images_delete', methods: ['DELETE'])]
    public function deleteImage(
        int $id,
        VehiclesImagesRepository $vehiclesImagesRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $image = $vehiclesImagesRepository->find($id);

        if (!$image) {
            return $this->json(['error' => 'Imagen no encontrada'], 404);
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $image->getImageUrl();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $em->remove($image);
        $em->flush();

        return $this->json(['message' => 'Imagen eliminada correctamente']);
    }
}