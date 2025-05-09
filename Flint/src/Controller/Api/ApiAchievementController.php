<?php

namespace App\Controller\Api;

use App\Repository\AchievementRepository;
use App\Repository\UserAchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class ApiAchievementController extends AbstractController
{
    #[Route('/achievements', name: 'achievements', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        AchievementRepository $achievementRepository,
        UserAchievementRepository $userAchievementRepository
    ): JsonResponse {
        $user = $this->getUser();

        $allAchievements = $achievementRepository->findAll();
        $unlocked = $userAchievementRepository->findBy(['appUser' => $user]);

        $unlockedCodes = array_map(
            fn($ua) => $ua->getAchievement()->getCode(),
            $unlocked
        );

        $data = array_map(function ($a) use ($unlockedCodes) {
            return [
                'code' => $a->getCode(),
                'name' => $a->getName(),
                'description' => $a->getDescription(),
                'rewards' => $a->getRewards(),
                'isPremium' => $a->isPremium(),
                'isUnlocked' => in_array($a->getCode(), $unlockedCodes),
            ];
        }, $allAchievements);

        return $this->json($data);
    }
}
