<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class StatsUpdater
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Met à jour le streak de l'utilisateur.
     * Retourne true si une nouvelle flamme est déclenchée aujourd'hui.
     */
    public function updateStreak(User $user): bool
    {
        $stats = $user->getStats();
        $now = new \DateTimeImmutable('today');
        $last = $stats->getLastActivity()?->setTime(0, 0);

        // Déjà mis à jour aujourd’hui
        if ($last && $last == $now) {
            error_log('[🔥 STREAK] Already updated today → no flame.');
            return false;
        }

        // Incrémenter ou reset
        if ($last && $last == $now->modify('-1 day')) {
            $stats->setStreak($stats->getStreak() + 1);
            error_log('[🔥 STREAK] Continuing streak: ' . $stats->getStreak());
        } else {
            $stats->setStreak(1);
            error_log('[🔥 STREAK] Streak reset to 1');
        }

        // Record personnel
        if ($stats->getStreak() > $stats->getMaxStreak()) {
            $stats->setMaxStreak($stats->getStreak());
            error_log('[🔥 STREAK] New max streak: ' . $stats->getMaxStreak());
        }

        $stats->setLastActivity($now);
        $this->em->flush();

        error_log('[🔥 STREAK] Flame triggered and saved!');

        return true;
    }
}
