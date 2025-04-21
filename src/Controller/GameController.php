<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
}
