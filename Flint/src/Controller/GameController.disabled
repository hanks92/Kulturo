<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\GardenPlant;
use App\Repository\UserStatsRepository;
use App\Repository\GardenPlantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserPlantInventoryRepository;

#[Route('/api/game', name: 'api_game_')]
class GameController extends AbstractController
{
    #[Route('/update-water', name: 'update_water', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateWater(Request $request, UserStatsRepository $userStatsRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $newWater = $data['water'] ?? null;

        if (!is_numeric($newWater)) {
            return new JsonResponse(['error' => 'Invalid water value'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();
        $userStats = $userStatsRepository->findOneBy(['user' => $user]);

        if (!$userStats) {
            return new JsonResponse(['error' => 'Stats non trouvées'], 404);
        }

        if ($newWater > $userStats->getWater()) {
            return new JsonResponse(['error' => 'Impossible d\'augmenter l\'eau manuellement'], 403);
        }

        $userStats->setWater($newWater);
        $em->flush();

        return new JsonResponse(['success' => true, 'water' => $newWater]);
    }

    #[Route('/update-plants', name: 'update_plants', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePlants(Request $request, EntityManagerInterface $em, GardenPlantRepository $gardenPlantRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $plants = $data['plants'] ?? [];

        if (!is_array($plants)) {
            return new JsonResponse(['error' => 'Invalid plants data'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();

        // Supprimer les anciennes plantes de l'utilisateur
        $gardenPlantRepository->deleteAllByUser($user);

        foreach ($plants as $plantData) {
            if (!isset($plantData['x'], $plantData['y'], $plantData['type'], $plantData['level'], $plantData['waterReceived'])) {
                continue;
            }

            $plant = new GardenPlant();
            $plant->setUserApp($user); // ✅ Correction ici
            $plant->setX($plantData['x']);
            $plant->setY($plantData['y']);
            $plant->setType($plantData['type']);
            $plant->setLevel($plantData['level']);
            $plant->setWaterReceived($plantData['waterReceived']);
            $em->persist($plant);
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/load-garden', name: 'load_garden', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function loadGarden(GardenPlantRepository $gardenPlantRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // ❗ Correction ici
        $plants = $gardenPlantRepository->findBy(['userApp' => $user]);

        $data = array_map(fn(GardenPlant $plant) => [
            'x' => $plant->getX(),
            'y' => $plant->getY(),
            'type' => $plant->getType(),
            'level' => $plant->getLevel(),
            'waterReceived' => $plant->getWaterReceived(),
        ], $plants);

        return new JsonResponse($data);
    }

    #[Route('/inventory', name: 'inventory', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getInventory(UserPlantInventoryRepository $inventoryRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $inventory = $inventoryRepository->findBy(['userApp' => $user]);

        $data = [];

        foreach ($inventory as $item) {
            $data[$item->getPlantType()] = $item->getQuantity();
        }

        return new JsonResponse($data);
    }

}
