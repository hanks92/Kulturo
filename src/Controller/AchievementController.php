<?php

namespace App\Controller;

use App\Repository\AchievementRepository;
use App\Repository\UserAchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AchievementController extends AbstractController
{
    #[Route('/achievements', name: 'user_achievements')]
    public function index(
        AchievementRepository $achievementRepository,
        UserAchievementRepository $userAchievementRepository
    ): Response {
        $user = $this->getUser();

        $allAchievements = $achievementRepository->findAll();
        $unlocked = $userAchievementRepository->findBy(['appUser' => $user]);

        $userAchievements = [];
        foreach ($unlocked as $ua) {
            $userAchievements[$ua->getAchievement()->getCode()] = $ua;
        }

        return $this->render('achievements/index.html.twig', [
            'allAchievements' => $allAchievements,
            'userAchievements' => $userAchievements
        ]);
    }
}
