<?php

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\User;
use App\Entity\UserAchievement;
use App\Repository\AchievementRepository;
use App\Repository\UserAchievementRepository;
use Doctrine\ORM\EntityManagerInterface;

class AchievementUnlocker
{
    public function __construct(
        private EntityManagerInterface $em,
        private AchievementRepository $achievementRepo,
        private UserAchievementRepository $userAchievementRepo,
    ) {}

    public function unlock(User $user, string $achievementCode): bool
    {
        $achievement = $this->achievementRepo->findOneBy(['code' => $achievementCode]);

        if (!$achievement) {
            return false;
        }

        $already = $this->userAchievementRepo->findOneBy([
            'user' => $user,
            'achievement' => $achievement
        ]);

        if ($already) {
            return false;
        }

        $userAchievement = new UserAchievement();
        $userAchievement->setUser($user);
        $userAchievement->setAchievement($achievement);
        $userAchievement->setAchievedAt(new \DateTimeImmutable());

        $this->em->persist($userAchievement);

        $this->applyRewards($user, $achievement->getRewards());

        $this->em->flush();

        return true;
    }

    private function applyRewards(User $user, array $rewards): void
    {
        $stats = $user->getStats();

        if (isset($rewards['xp'])) {
            $stats->setTotalXp($stats->getTotalXp() + $rewards['xp']);
        }

        // Other reward types (trees, mushrooms, etc.) can go here
    }
}
